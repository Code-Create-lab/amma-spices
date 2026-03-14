<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\OrderPayments;
use App\Models\Payment;
use App\Models\Orders;
use App\Models\StoreOrders;
use App\Models\Variation;
use App\Services\PayGlocalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckPendingOrdersCommand extends Command
{
    protected $signature = 'orders:check-pending {--age=30 : Check orders older than X minutes}';
    protected $description = 'Check pending orders and verify payment status from PayGlocal';

    private $payGlocalService;

    public function __construct(PayGlocalService $payGlocalService)
    {
        parent::__construct();
        $this->payGlocalService = $payGlocalService;
    }

    public function handle()
    {
        $this->info('Starting to check pending PayGlocal orders...');

        $ageMinutes = $this->option('age');

        // Fetch orders with pending/Pending status that are older than specified minutes
        $pendingOrders = Orders::
            // where(function($query) {
            //         $query->where('order_status', 'Pending')
            //               ->orWhere('order_status', 'pending');
            //     })
            // ->where(function($query) {
            //     $query->where('payment_status', 'Pending')
            //           ->orWhere('payment_status', 'pending');
            // })
            // ->where('time_slot', '<=', now()->subMinutes($ageMinutes))
            orderBy('time_slot', 'desc')
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending orders found.');
            return 0;
        }

        $this->info("Found {$pendingOrders->count()} pending order(s) older than {$ageMinutes} minutes.");

        $processed = 0;
        $succeeded = 0;
        $failed = 0;
        $skipped = 0;
        // dd($pendingOrders);
        foreach ($pendingOrders as $order) {
            try {
                // Get payment record to find PayGlocal GID
                $payment = OrderPayments::where('order_id', $order->order_id)->first();
                // dd($payment);
                if (!$payment || !$payment->gid) {
                    $this->warn("No PayGlocal GID found for Order ID: {$order->order_id}");
                    $skipped++;
                    continue;
                }

                $this->info("Checking Order ID: {$order->order_id} | GID: {$payment->gid}");


                $refundData = [
                    'order_id' => $payment->order_id,
                    'type' => "F", // Full refund for cancellation
                    'currency' => 'INR',
                    'amount' => $order->total_price
                ];
                // Fetch payment status from PayGlocal
                $statusResponse = $this->payGlocalService->getPaymentStatus($payment->gid, $refundData);

                if (!isset($statusResponse['status'])) {
                    $this->warn("Invalid status response for Order ID: {$order->order_id}");
                    $skipped++;
                    continue;
                }

                $paymentStatus = strtoupper($statusResponse['status']);
                $this->info("PayGlocal Status: {$paymentStatus}");

                // Process based on PayGlocal payment status
                DB::beginTransaction();

                switch ($paymentStatus) {
                    case 'SUCCESS':
                    case 'SENT_FOR_CAPTURE':
                    case 'COMPLETED':
                        // Payment successful - reduce stock
                        $order->payment_status = 'paid';
                        $order->order_status = 'Pending';
                        $order->paid_at = now();

                        // Update payment record
                        $payment->payment_status = strtoupper($paymentStatus);
                        $payment->gateway_response = $statusResponse;
                        $payment->processed_at = now();

                        // Reduce stock for all items
                        $storeOrders = StoreOrders::where('order_cart_id', $order->cart_id)->get();
                        foreach ($storeOrders as $storeOrder) {
                            $updated = Variation::where('id', $storeOrder->varient_id)
                                ->where('stock', '>=', $storeOrder->quantity)
                                ->decrement('stock', $storeOrder->quantity);

                            if (!$updated) {
                                $this->warn("Insufficient stock for variation ID: {$storeOrder->varient_id}");
                            }
                        }

                        // Clear cart
                        if ($order->user_id) {
                            Cart::where('user_id', $order->user_id)->delete();
                        }

                        $order->save();
                        $payment->save();

                        DB::commit();

                        $this->info("✓ Order {$order->order_id} marked as PAID");
                        $succeeded++;
                        break;

                    case 'FAILED':
                    case 'FAILURE':
                    case 'CUSTOMER_CANCELLED':
                    case 'AUTHENTICATION_TIMEOUT':
                    case 'ISSUER_DECLINE':
                    case 'GENERAL_DECLINE':
                        // Payment failed - mark as failed, don't reduce stock
                        $order->payment_status = 'failed';
                        $order->order_status = 'failed';

                        // Update payment record
                        $payment->payment_status = strtoupper($paymentStatus);
                        $payment->gateway_response = $statusResponse;
                        $payment->failure_reason = $statusResponse['failureReason'] ?? $paymentStatus;
                        $payment->processed_at = now();

                        $order->save();
                        $payment->save();

                        DB::commit();

                        $this->warn("✗ Order {$order->order_id} marked as FAILED");
                        $failed++;
                        break;

                    case 'PENDING':
                    case 'PROCESSING':
                    default:
                        // Still pending - no action needed
                        DB::commit();
                        $this->info("○ Order {$order->order_id} still PENDING");
                        $skipped++;
                        break;
                }

                $processed++;
            } catch (Exception $e) {
                DB::rollBack();
                $this->error("Error processing Order ID {$order->order_id}: {$e->getMessage()}");
                Log::error('CheckPendingOrders Error', [
                    'order_id' => $order->order_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $skipped++;
                continue;
            }
        }

        // Summary
        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Total Processed: {$processed}");
        $this->info("Succeeded: {$succeeded}");
        $this->info("Failed: {$failed}");
        $this->info("Skipped: {$skipped}");
        $this->info('Finished checking pending orders.');

        return 0;
    }
}
