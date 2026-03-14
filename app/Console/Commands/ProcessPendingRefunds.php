<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Orders;
use App\Models\OrderPayments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessPendingRefunds extends Command
{
    protected $signature = 'refunds:process
                            {--limit=50 : Maximum refunds to process in one run}
                            {--dry-run : Simulate refund processing without DB updates}';

    protected $description = 'Process pending refunds when balance is available';

    public function handle()
    {
        $limit  = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        $this->info('Starting pending refund processing');
        $this->info("Limit: {$limit}");


          Log::info('Processing pending refunds', [
            'limit' => $limit,
            'dry_run' => $dryRun,
            'datetime' => Carbon::now()->toDateTimeString()
        ]);
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No refunds will be executed');
        }

        $this->newLine();

        $pendingRefunds = DB::table('pending_refunds')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($pendingRefunds->isEmpty()) {
            $this->info('✓ No pending refunds found');
            return 0;
        }

        $this->warn("Found {$pendingRefunds->count()} pending refunds");
        $this->newLine();

        $processed = 0;
        $failed    = 0;

        $bar = $this->output->createProgressBar($pendingRefunds->count());
        $bar->start();

        foreach ($pendingRefunds as $refund) {
            try {
                $this->processSingleRefund($refund, $dryRun);
                $processed++;
            } catch (\Exception $e) {
                $failed++;

                Log::error('Refund processing failed', [
                    'refund_id' => $refund->id,
                    'order_id'  => $refund->order_id,
                    'error'     => $e->getMessage(),
                ]);

                $this->newLine();
                $this->error("Refund failed for order #{$refund->order_id}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Refunds Processed', $processed],
                ['Refunds Failed', $failed],
                ['Total Checked', $pendingRefunds->count()],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY RUN COMPLETE - No changes were made');
        }

        Log::info('Pending refund processing completed', [
            'processed' => $processed,
            'failed'    => $failed,
            'dry_run'   => $dryRun,
        ]);

        return 0;
    }

    /**
     * Process a single refund safely
     */
    private function processSingleRefund($refund, bool $dryRun = false)
    {
        $order = Orders::where('order_id', $refund->order_id)->first();
        $payment = OrderPayments::where('id', $refund->payment_id)->first();

        if (!$order) {
            throw new \Exception('Order not found');
        }

        if (!$payment) {
            throw new \Exception('Payment record not found');
        }

        // Idempotency check
        if ($payment->payment_status === 'REFUNDED') {
            Log::warning('Refund already processed', [
                'refund_id' => $refund->id,
                'order_id'  => $refund->order_id,
            ]);

            DB::table('pending_refunds')
                ->where('id', $refund->id)
                ->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);

            return;
        }

        if ($dryRun) {
            Log::info('DRY RUN: Would process refund', [
                'refund_id' => $refund->id,
                'order_id'  => $refund->order_id,
                'amount'    => $refund->amount ?? null,
            ]);
            return;
        }

        DB::beginTransaction();

        try {
            /**
             * 👉 Razorpay refund logic goes here
             * Example:
             * $razorpay->payment->fetch($payment->payment_id)
             *     ->refund(['amount' => $refund->amount * 100]);
             */

            // Mark payment refunded
            $payment->update([
                'payment_status' => 'REFUNDED',
                'updated_at'     => now(),
            ]);

            // Update pending_refunds
            DB::table('pending_refunds')
                ->where('id', $refund->id)
                ->update([
                    'status'       => 'completed',
                    'processed_at' => now(),
                    'updated_at'   => now(),
                ]);

            DB::commit();

            Log::info('Refund processed successfully', [
                'refund_id' => $refund->id,
                'order_id'  => $refund->order_id,
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
