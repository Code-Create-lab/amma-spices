<?php

// app/Jobs/ProcessIthinkTrackingUpdate.php
namespace App\Jobs;

use App\Models\WebhookLog;
use App\Models\Order; // your order/shipment model
use App\Models\Orders;
use App\Models\ShipmentTracking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ProcessIthinkTrackingUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payload;
    public $signature;

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
       
        // Normalize/parse times to UTC (if provided). Keep null if parsing fails.
        $latestScanTime = null;
        $eddDate = null;
        try {
            if (!empty($scanTimeRaw)) {
                // Accept many common formats; assume incoming time is in server/local timezone if no zone provided.
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

        // Idempotency check 2: if shipment_trackings already has this AWB + latest_scan_time, skip to avoid duplicate timeline entries
        if ($latestScanTime) {
            $alreadyTracking = ShipmentTracking::where('awb_number', $awb)
                ->where('latest_scan_time', $latestScanTime)
                ->exists();

            if ($alreadyTracking) {
                // Mark the corresponding webhook log as processed if exists
                $matchingLog = WebhookLog::where('awb_number', $awb)
                    ->where('payload->latest_scan_time', $latestScanTime)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($matchingLog && ! $matchingLog->processed) {
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
            // Mark the most relevant WebhookLog row as processed (if it exists)
            $logQuery = WebhookLog::where('awb_number', $awb);

            if ($latestScanTime) {
                $logQuery->where('payload->latest_scan_time', $latestScanTime);
            }
            $log = $logQuery->orderBy('id', 'desc')->first();

            if ($log) {
                $log->processed = true;
                $log->save();
            }

            // Insert into shipment_trackings (use firstOrCreate to avoid race-condition duplicates)
            $trackingAttributes = [
                'awb_number' => $awb,
                'latest_scan_time' => $latestScanTime, // may be null
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

            // If latest_scan_time is null, use a fallback unique key (timestamp + awb would be unreliable),
            // so we attempt a plain create and handle duplicate exception.
            if ($latestScanTime) {
                ShipmentTracking::firstOrCreate($trackingAttributes, $trackingValues);
            } else {
                // no latestScanTime — create a new record (could produce duplicates if provider retries with no timestamp)
                ShipmentTracking::create(array_merge($trackingAttributes, $trackingValues));
            }

            DB::commit();
        } catch (\Illuminate\Database\QueryException $qe) {
            DB::rollBack();
            // Handle duplicate key race-condition gracefully (unique index on awb+latest_scan_time)
            if ($qe->getCode() == '23000') {
                Log::info("Duplicate DB entry detected while processing AWB {$awb} at {$latestScanTime}.", [
                    'exception' => $qe->getMessage(),
                ]);
                return;
            }

            Log::error('Database error processing webhook: ' . $qe->getMessage(), ['payload' => $data]);
            return;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error processing webhook payload: ' . $e->getMessage(), ['payload' => $data]);
            return;
        }

        // Optionally: dispatch any notification jobs outside of transaction to keep transaction short
        // Example: NotifyCustomerTrackingUpdate::dispatch($order->id, $shipmentTracking->id);

        Log::info("Processed webhook for AWB {$awb}" . ($latestScanTime ? " at {$latestScanTime}" : ''));
    }
}
