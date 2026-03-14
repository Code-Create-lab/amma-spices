<?php

namespace App\Jobs;

use App\Models\WebhookLog;
use App\Models\Orders;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use App\Services\RazorpayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ProcessIthinkTrackingUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payload;
    public $signature;

    // Retry configuration
    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min
    public $timeout = 120;

    public function __construct(array $payload, ?string $signature = null)
    {
        $this->payload = $payload;
        $this->signature = $signature;
    }

    public function handle()
    {
        $awb = $this->payload['awb_number'] ?? null;
        $scanTimeRaw = $this->payload['latest_scan_time'] ?? null;
        $data = $this->payload;
       
        // Normalize/parse times to UTC
        $latestScanTime = null;
        $eddDate = null;
        
        try {
            if (!empty($scanTimeRaw)) {
                $latestScanTime = Carbon::parse($scanTimeRaw)->setTimezone('UTC')->toDateTimeString();
            }
        } catch (\Throwable $e) {
            Log::warning("Unable to parse latest_scan_time for AWB {$awb}: {$scanTimeRaw}");
        }

        try {
            if (!empty($data['edd_date'])) {
                $eddDate = Carbon::parse($data['edd_date'])->setTimezone('UTC')->toDateTimeString();
            }
        } catch (\Throwable $e) {
            Log::warning("Unable to parse edd_date for AWB {$awb}: " . $data['edd_date']);
        }

        // Basic sanity check
        if (empty($awb)) {
            Log::error('Webhook payload missing awb_number; skipping.', ['payload' => $data]);
            return;
        }

        // Idempotency check 1: if we've already processed a WebhookLog with this AWB + scan time, skip
        $existsProcessed = false;
        if ($latestScanTime) {
            $existsProcessed = WebhookLog::where('awb_number', $awb)
                ->where('payload->latest_scan_time', $latestScanTime)
                ->where('processed', true)
                ->exists();
        }

        if ($existsProcessed) {
            Log::info("Duplicate webhook (already processed) for AWB {$awb} at {$latestScanTime}, skipping.");
            return;
        }

        // Idempotency check 2: if shipment_trackings already has this AWB + latest_scan_time, skip
        if ($latestScanTime) {
            $alreadyTracking = ShipmentTracking::where('awb_number', $awb)
                ->where('latest_scan_time', $latestScanTime)
                ->exists();

            if ($alreadyTracking) {
                $matchingLog = WebhookLog::where('awb_number', $awb)
                    ->where('payload->latest_scan_time', $latestScanTime)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($matchingLog && !$matchingLog->processed) {
                    $matchingLog->processed = true;
                    $matchingLog->save();
                }

                Log::info("ShipmentTracking already exists for AWB {$awb} at {$latestScanTime}, skipping insert.");
                return;
            }
        }

        // All DB writes in transaction for consistency
        DB::beginTransaction();
        try {
            // Mark the most relevant WebhookLog row as processed
            $logQuery = WebhookLog::where('awb_number', $awb);

            if ($latestScanTime) {
                $logQuery->where('payload->latest_scan_time', $latestScanTime);
            }
            $log = $logQuery->orderBy('id', 'desc')->first();

            if ($log) {
                $log->processed = true;
                $log->save();
            }

            // Insert into shipment_trackings
            $trackingAttributes = [
                'awb_number' => $awb,
                'latest_scan_time' => $latestScanTime,
            ];

            $trackingValues = [
                'order_id' => $data['order_id'] ?? null,
                'logistics_name' => $data['logistics_name'] ?? null,
                'current_tracking_status' => $data['current_tracking_status'] ?? null,
                'status' => $data['status'] ?? null,
                'remark' => $data['remark'] ?? null,
                'location' => $data['location'] ?? null,
                'edd_date' => $eddDate,
                'tracking_url' => $data['tracking_url'] ?? null,
                'raw_payload' => json_encode($data),
            ];

            $shipmentTracking = null;
            if ($latestScanTime) {
                $shipmentTracking = ShipmentTracking::firstOrCreate($trackingAttributes, $trackingValues);
            } else {
                $shipmentTracking = ShipmentTracking::create(array_merge($trackingAttributes, $trackingValues));
            }

            // Find shipment by AWB number from shipments table
            $shipment = Shipment::where('waybill', $awb)->first();

            if ($shipment) {
                // Update shipment status
                $shipment->update([
                    'status' => $data['current_tracking_status'] ?? $shipment->status,
                    'remark' => $data['remark'] ?? $shipment->remark,
                    'updated_at' => now()
                ]);

                // Get the order through the relationship
                $order = $shipment->order;

                if ($order) {
                    Log::info("Order found for AWB {$awb}: Order ID {$order->order_id}");

                    // Check if we need to trigger refund
                    $currentStatus = $data['current_tracking_status'] ?? '';
                    if ($this->shouldTriggerRefund($currentStatus)) {
                        Log::info("Refund trigger detected for AWB {$awb}, Order ID: {$order->order_id}, status: {$currentStatus}");
                        $this->processRefund($order, $awb, $shipment);
                    }
                } else {
                    Log::warning("Order not found for shipment with AWB: {$awb}, shipment_id: {$shipment->id}");
                }
            } else {
                Log::warning("Shipment not found for AWB: {$awb}");
            }

            DB::commit();
        } catch (\Illuminate\Database\QueryException $qe) {
            DB::rollBack();
            
            // Handle duplicate key race-condition gracefully
            if ($qe->getCode() == '23000') {
                Log::info("Duplicate DB entry detected while processing AWB {$awb} at {$latestScanTime}.", [
                    'exception' => $qe->getMessage(),
                ]);
                return;
            }

            Log::error('Database error processing webhook: ' . $qe->getMessage(), ['payload' => $data]);
            throw $qe; // Re-throw to trigger retry
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error processing webhook payload: ' . $e->getMessage(), [
                'payload' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw to trigger retry
        }

        Log::info("Processed webhook for AWB {$awb}" . ($latestScanTime ? " at {$latestScanTime}" : ''));
    }

    /**
     * Determine if the current status should trigger a refund
     */
    private function shouldTriggerRefund(string $status): bool
    {
        // Normalize status for comparison
        $status = strtoupper(trim($status));
        
        // Define statuses that trigger refund (adjust based on ithinklogistics documentation)
        $refundStatuses = [
            'CANCELLED',
            'ORDER_CANCELLED',
            'RTO',                          // Return to Origin
            'RTO_DELIVERED',                // Return to Origin Delivered
            'RTO_IN_TRANSIT',               // Return to Origin In Transit (optional)
            'RETURNED_TO_WAREHOUSE',
            'SHIPMENT_CANCELLED',
            'DELIVERY_CANCELLED',
            'CUSTOMER_REFUSED',
            'UNDELIVERED',
            'RTO_RECEIVED',
        ];
        
        return in_array($status, $refundStatuses);
    }

    /**
     * Process refund for the order
     */
    private function processRefund($order, $awb = null, $shipment = null)
    {
        // Prevent duplicate refunds - check order refund status
        if (in_array($order->refund_status ?? '', ['SUCCESS', 'PROCESSING', 'COMPLETED'])) {
            Log::info("Refund already processed/processing for order: {$order->order_id}");
            return;
        }

        // Check if payment_id exists
        if (empty($order->payment_id)) {
            Log::warning("Order {$order->order_id} has no payment_id, cannot process refund");
            return;
        }

        // Check if already refunded in order_refunds table
        $existingRefund = DB::table('order_refunds')
            ->where('payment_id', $order->payment_id)
            ->whereIn('refund_status', ['SUCCESS', 'PROCESSING', 'COMPLETED'])
            ->first();
        
        if ($existingRefund) {
            Log::info("Refund already exists in order_refunds for payment: {$order->payment_id}");
            
            // Update order status if not already updated
            if (!in_array($order->refund_status ?? '', ['SUCCESS', 'COMPLETED'])) {
                $order->update(['refund_status' => 'SUCCESS']);
            }
            return;
        }

        // Mark as processing to prevent race conditions
        try {
            $order->update(['refund_status' => 'PROCESSING']);
        } catch (\Throwable $e) {
            Log::error("Failed to update order refund_status to PROCESSING: {$e->getMessage()}");
            return;
        }

        // Process refund
        try {
            $razorpayService = app(RazorpayService::class);
            
            // Determine refund amount (in paise for Razorpay)
            $refundAmount = $this->calculateRefundAmount($order);
            
            Log::info("Initiating refund for order {$order->order_id}", [
                'payment_id' => $order->payment_id,
                'amount' => $refundAmount,
                'awb' => $awb ?? 'N/A'
            ]);

            $response = $razorpayService->refundPayment($order->payment_id, [
                'amount' => $refundAmount, // Amount in paise
                'speed' => 'normal', // or 'optimum'
                'notes' => [
                    'order_id' => $order->order_id,
                    'awb_number' => $awb ?? 'N/A',
                    'reason' => 'Order cancelled - returned to warehouse',
                    'tracking_status' => $this->payload['current_tracking_status'] ?? ''
                ],
                'receipt' => 'RFD-' . $order->order_id . '-' . time()
            ]);

            if (isset($response['success']) && $response['success']) {
                // Update order status
                $order->update([
                    'refund_status' => 'SUCCESS',
                    'refund_date' => now(),
                    'order_status' => 'CANCELLED' // or your preferred status
                ]);

                // Update shipment status to cancelled/refunded
                if ($shipment) {
                    $shipment->update([
                        'status' => 'CANCELLED',
                        'refnum' => $response['data']['id'] ?? null, // Razorpay refund ID
                        'response' => json_encode($response),
                        'updated_at' => now()
                    ]);
                }

                // Insert refund record
                DB::table('order_refunds')->insert([
                    'order_id' => $order->order_id,
                    'payment_id' => $order->payment_id,
                    'refund_amount' => $refundAmount / 100, // Convert paise to rupees
                    'refund_status' => 'SUCCESS',
                    'refund_response' => json_encode($response),
                    'transaction_reference' => 'RFD-' . strtoupper(Str::random(10)),
                    'reason' => 'Order cancelled - AWB: ' . ($awb ?? 'N/A'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info("Refund successful for order: {$order->order_id}, payment: {$order->payment_id}");
                
                // Optional: Dispatch notification event
                // event(new RefundProcessed($order));
                
            } else {
                // Refund failed
                $order->update(['refund_status' => 'FAILED']);
                
                if ($shipment) {
                    $shipment->update([
                        'response' => json_encode($response),
                    ]);
                }
                
                Log::error('Refund failed for order: ' . $order->order_id, [
                    'response' => $response,
                    'payment_id' => $order->payment_id
                ]);

                // Optional: Notify admin about failed refund
                // event(new RefundFailed($order, $response));
            }

        } catch (\Exception $e) {
            $order->update(['refund_status' => 'FAILED']);
            
            Log::error('Refund exception for order: ' . $order->order_id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payment_id' => $order->payment_id
            ]);

            // Optional: Notify admin about exception
            // event(new RefundException($order, $e));
        }
    }

    /**
     * Calculate the refund amount for the order
     */
    private function calculateRefundAmount($order): int
    {
        // Get the paid amount from order
        // Adjust field name based on your orders table structure
        $paidAmount = $order->total_amount ?? $order->amount ?? $order->paid_amount ?? 0;
        
        // Convert to paise (Razorpay expects amount in smallest currency unit)
        // For INR: 1 Rupee = 100 paise
        $amountInPaise = (int) ($paidAmount * 100);
        
        return $amountInPaise;
    }
}