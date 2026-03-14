<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Orders;
use App\Models\Cart;
use App\Models\StoreOrders;
use App\Models\Variation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentFailed;
use App\Models\OrderPayments;
use Carbon\Carbon;

class MarkAbandonedOrdersFailed extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'orders:mark-failed 
                            {--minutes=15 : Mark orders failed after X minutes}
                            {--limit=100 : Maximum number of orders to process}
                            {--dry-run : Simulate without actually updating}';

    /**
     * The console command description.
     */
    protected $description = 'Mark online orders as failed when payment data is not available after timeout';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = $this->option('minutes');
        $limit = $this->option('limit');
        $dryRun = $this->option('dry-run');
        $now = Carbon::now('Asia/Kolkata');

        Log::info('Marking abandoned orders as failed', [
            'limit' => $limit,
            'minutes' => $minutes,
            'dry_run' => $dryRun,
            'datetime' => Carbon::now()->toDateTimeString()
        ]);
        $this->info("Checking for abandoned orders without payment data...");
        $this->info("Time threshold: {$minutes} minutes");
        $this->info("Limit: {$limit} orders");
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No actual changes will be made");
        }
        $this->newLine();

        // Find orders that:
        // 1. Payment method is online (NOT COD)
        // 2. Created more than X minutes ago
        // 3. Don't have payment record in order_payments table
        // 4. Order status is not already failed/cancelled/completed
        // 5. Payment status is not already paid/success/failed

        $abandonedOrders = Orders::with(['user', 'orderItems', 'address'])
            ->where('payment_method', '!=', 'COD') // Only online payment orders
            ->where('order_date', '>=', Carbon::now()->subMinutes($minutes))
            ->whereNotIn('order_status', ['failed', 'cancelled', 'completed'])
            ->whereNotIn('payment_status', ['paid', 'success', 'failed'])
            ->whereDoesntHave('payment') // Orders WITHOUT payment record in order_payments table
            ->whereRaw(
                "STR_TO_DATE(CONCAT(order_date, ' ', order_time), '%Y-%m-%d %H:%i:%s') <= ?",
                [now()->subMinutes(25)->toDateTimeString()]
            )
            ->orderBy('order_date', 'asc')
            ->limit($limit)
            ->get();

        if ($abandonedOrders->isEmpty()) {
            $this->info('✓ No abandoned orders without payment data found');
            return 0;
        }

        $this->warn("Found {$abandonedOrders->count()} abandoned orders without payment data");
        $this->newLine();

        $processed = 0;
        $stockRestored = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($abandonedOrders->count());
        $progressBar->start();

        foreach ($abandonedOrders as $order) {
            try {
                $this->markOrderAsFailed($order, $dryRun);

                $processed++;

                // Count items for stock restoration
                if ($order->orderItems) {
                    $stockRestored += $order->orderItems->count();
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error processing order #{$order->order_id}: {$e->getMessage()}");

                Log::error('Failed to mark abandoned order as failed', [
                    'order_id' => $order->order_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info("Summary:");
        $this->table(
            ['Status', 'Count'],
            [
                ['Orders Marked as Failed', $processed],
                ['Items Stock Restored', $stockRestored],
                ['Errors', $errors],
                ['Total Processed', $abandonedOrders->count()],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN COMPLETE - No actual changes were made');
            $this->info('Run without --dry-run flag to actually mark orders as failed');
        }

        Log::info('Abandoned orders marked as failed completed', [
            'processed' => $processed,
            'stock_restored' => $stockRestored,
            'errors' => $errors,
            'total' => $abandonedOrders->count(),
            'dry_run' => $dryRun
        ]);

        return 0;
    }

    /**
     * Mark a single abandoned order as failed
     */
    private function markOrderAsFailed($order, $dryRun = false)
    {
        if ($dryRun) {
            Log::info('DRY RUN: Would mark order as failed', [
                'order_id' => $order->order_id,
                'cart_id' => $order->cart_id,
                'order_date' => $order->order_date,
                'payment_method' => $order->payment_method,
                'razorpay_order_id' => $order->razorpay_order_id ?? 'N/A'
            ]);
            return;
        }

        DB::beginTransaction();

        try {
            // 1. Update order status to failed
            $order->update([
                'order_status' => 'failed',
                'payment_status' => 'failed',
                'updated_at' => now(),
            ]);

            // 2. Restore stock for all items
            // foreach ($order->orderItems as $item) {
            //     if ($item->variation_id) {
            //         Variation::where('id', $item->variation_id)
            //             ->increment('stock', $item->quantity);

            //         Log::info('Stock restored for abandoned order', [
            //             'order_id' => $order->order_id,
            //             'variation_id' => $item->variation_id,
            //             'quantity' => $item->quantity
            //         ]);
            //     }
            // }

            // 3. Create payment record as FAILED in order_payments table
            $paymentId = $order->razorpay_payment_id ?? ('abandoned_' . $order->order_id . '_' . time());

            OrderPayments::insert(
                [
                    'order_id' => $order->order_id,
                    'payment_id' => $paymentId,
                    'mode' => 'razorpay',
                    'transaction_number' => $order->razorpay_order_id ?? null,
                    'transaction_date' => now(),
                    'method' => $order->payment_method ?? 'razorpay',
                    'currency' => 'INR',
                    'amount' => $order->total_price ?? 0,
                    'payment_status' => 'FAILED',
                    'provider_reference_id' => 'BTX-' . strtoupper(\Illuminate\Support\Str::random(12)),
                    'json_response' => json_encode([
                        'status' => 'abandoned',
                        'reason' => 'Payment not completed - No payment data available',
                        'order_id' => $order->order_id,
                        'razorpay_order_id' => $order->razorpay_order_id ?? null,
                        'order_date' => $order->order_date,
                        'marked_failed_at' => now(),
                    ]),
                    'transaction_type' => 'FAILED',
                    'failure_reason' => 'Payment not completed - User closed payment page without completing transaction',
                    'notes' => 'Automatically marked as failed due to no payment data after ' . $this->option('minutes') . ' minutes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::commit();

            // 4. Send payment failed email to user
            if ($order->user && $order->user->email) {
                try {
                    Mail::to($order->user->email)->send(new PaymentFailed($order));

                    Log::info('Payment failed email sent for abandoned order', [
                        'order_id' => $order->order_id,
                        'email' => $order->user->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send payment failed email', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Abandoned order marked as failed successfully', [
                'order_id' => $order->order_id,
                'cart_id' => $order->cart_id,
                'order_date' => $order->order_date,
                'marked_failed_at' => now(),
                'items_count' => $order->orderItems->count(),
                'razorpay_order_id' => $order->razorpay_order_id ?? 'N/A'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
