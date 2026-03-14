<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Orders;
use App\Jobs\CheckRazorpayPaymentStatusJob;
use App\Mail\PaymentFailed;
use App\Mail\PaymentSuccess;
use App\Models\Cart;
use App\Models\OrderPayments;
use App\Models\StoreOrders;
use App\Models\User;
use App\Models\Variation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Razorpay\Api\Api;

class CheckPendingPayments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payments:check-pending 
                            {--limit=50 : Maximum number of orders to check}
                            {--days=2 : Only check orders from last X days}';

    /**
     * The console command description.
     */
    protected $description = 'Check and update status of pending Razorpay payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $days = $this->option('days');
        $now = Carbon::now('Asia/Kolkata');

        Log::info('Pending payments check started', [
            'limit' => $limit,
            'days' => $days,
            'datetime' => Carbon::now()->toDateTimeString()
        ]);
        $this->info("Checking pending payments...");
        $this->info("Limit: {$limit} orders");
        $this->info("Time range: Last {$days} days");
        $this->newLine();

        // Get pending orders with payment records
        $pendingOrders = Orders::with('payment')
            // ->whereHas('payment') // Only orders that have payment records
            ->whereIn('payment_status', ['Pending', 'created', 'initiated', 'Refund Pending', 'refund_initiated', 'pending', 'Refund Failed'])
            ->where('order_date', '>=', Carbon::now()->subDays($days))
            ->whereRaw(
                "STR_TO_DATE(CONCAT(order_date, ' ', order_time), '%Y-%m-%d %H:%i:%s') <= ?",
                [now()->subMinutes(15)->toDateTimeString()]
            )
            ->orderBy('order_date', 'desc')
            ->limit($limit)
            ->get();
        // dd($pendingOrders);
        if ($pendingOrders->isEmpty()) {
            $this->info('✓ No pending payments found');
            return 0;
        }

        $this->info("Found {$pendingOrders->count()} pending orders");
        $this->newLine();

        $dispatched = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($pendingOrders->count());
        $progressBar->start();

        foreach ($pendingOrders as $order) {
            try {
                // Check if payment record exists
                if (!$order->payment || !$order->payment->payment_id) {
                    // $this->newLine();
                    // $this->warn("Order #{$order->order_id} has no payment_id, skipping");
                    // $skipped++;
                    // $progressBar->advance();
                    // continue;
                    $orderId = $order->razorpay_order_id;
                    Log::info('razorpay order id',['order_id' => $order->order_id,'razorpay_order_id' => $order->razorpay_order_id]);
                    if(!empty($orderId))
                    {
                        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
                        $payments = $api->order->fetch($orderId)->payments();
                        Log::info('Fetch payments for razorpay order id',['order_id' => $order->order_id,'razorpay_order_id' => $order->razorpay_order_id,'payment' => $payments]);

                        /**
                         * -------------------------------------
                         * CASE 2: No payments found → failed
                        * -------------------------------------
                        */
                        if (count($payments->items) === 0) {

                            // no payment found, mark as failed
                            // $order->order_status = 'failed';
                            // $order->payment_status = 'failed';
                            // $order->save();

                            $this->markOrderAsFailed($order);

                            Log::info('No payment Found for razorpay order id',['order_id' => $order->order_id,'razorpay_order_id' => $order->razorpay_order_id,'payment' => $payments]);

                        }

                        $payment = collect($payments->items)->where('status', 'captured')->first();

                        Log::info('Fetch payment items for razorpay order id',['order_id' => $order->order_id,'razorpay_order_id' => $order->razorpay_order_id,'payment' => $payment, 'payment items' => $payments->items]);

                        if ($payment) {
                            Log::info('payment Found for razorpay order id',['order_id' => $order->order_id,'razorpay_order_id' => $order->razorpay_order_id,'payment' => $payments]);

                            // CheckRazorpayPaymentStatusJob::dispatch(
                            //     $order->order_id,
                            //     $payment['id']
                            // );

                            $this->handlePaymentSuccess($order,$payment['id']);

                            $dispatched++;
                            
                        }
                        else{
                            /**
                             * -------------------------------------
                             * CASE 4: Payment NOT captured → failed
                             * -------------------------------------
                             */
                            // Payment not captured -> mark as failed
                            // $order->payment_status = 'failed';
                            // $order->order_status = 'failed';
                            // $order->save();
                             $this->markOrderAsFailed($order);
                        }
                    }
                }

                else{

                    // Dispatch job to check payment status
                    CheckRazorpayPaymentStatusJob::dispatch(
                        $order->order_id,
                        $order->payment->payment_id
                    );

                    $dispatched++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error dispatching job for order #{$order->order_id}: {$e->getMessage()}");

                Log::error('Failed to dispatch payment check job from cron', [
                    'order_id' => $order->order_id,
                    'error' => $e->getMessage()
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
                ['Jobs Dispatched', $dispatched],
                ['Skipped', $skipped],
                ['Errors', $errors],
                ['Total Checked', $pendingOrders->count()],
            ]
        );

        Log::info('Pending payments check completed', [
            'dispatched' => $dispatched,
            'skipped' => $skipped,
            'errors' => $errors,
            'total' => $pendingOrders->count()
        ]);

        return 0;
    }

    
    /**
     * Mark a single abandoned order as failed
     */
    private function markOrderAsFailed($order)
    {

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
                    'failure_reason' => 'Payment not completed',
                    'notes' => 'Automatically marked as failed due to no payment data ',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::commit();

            // 4. Send payment failed email to user
            if ($order->user && $order->user->email) {
                try {
                    Mail::to($order->user->email)->send(new PaymentFailed($order));

                    Log::info('Payment failed email sent', [
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

            Log::info('order marked as failed successfully', [
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

    /**
     * Handle payment success
     */
    private function handlePaymentSuccess($order, $paymentId)
    {
        try {
            DB::beginTransaction();

            // Update order
            $order->update([
                'payment_status' => 'paid',
                'order_status'   => 'Pending',
                'paid_at'        => now(),
            ]);

            // Generate provider reference
            $providerReference = 'BTX-' . strtoupper(Str::random(12));

            // Insert/Update payment record
            DB::table('order_payments')->updateOrInsert(
                ['payment_id' => $paymentId, 'order_id' => $order->order_id],
                [
                    'order_id'              => $order->order_id,
                    'payment_id'            => $paymentId,
                    'mode'                  => 'razorpay',
                    'transaction_number'    => $order->razorpay_order_id,
                    'transaction_date'      => now(),
                    'method'                => $order->payment_method ?? 'razorpay',
                    'currency'              => 'INR',
                    'amount'                => $order->total_price ?? 0,
                    'payment_status'        => 'Paid',
                    'provider_reference_id' => $providerReference,
                    'json_response'         => json_encode([
                        'status' => 'Paid',
                        'reason' => 'Payment status changed via cron',
                        'order_id' => $order->order_id,
                        'razorpay_order_id' => $order->razorpay_order_id ?? null,
                        'order_date' => $order->order_date,
                        'marked_paid_at' => now(),
                    ]),
                    'transaction_type'      => 'CAPTURED',
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]
            );

            // Reduce stock
            $storeOrder = StoreOrders::where('order_cart_id', $order->cart_id)->first();
            if ($storeOrder) {
                Variation::where('id', $storeOrder->varient_id)
                    ->decrement('stock', $storeOrder->quantity);
            }

            // Clear user cart
            Cart::where('user_id', $order->user_id)->delete();

            DB::commit();

            // Send success email
            $user = User::find($order->user_id);
            if ($user) {
                try {
                    if ($order->address->receiver_email != $user->email) {
                        Mail::to([$order->address->receiver_email, $user->email])
                            ->send(new PaymentSuccess($order));
                    } else {
                        Mail::to([$order->address->receiver_email])
                            ->send(new PaymentSuccess($order));
                    }
                    
                    Log::info('Payment success email sent', [
                        'order_id' => $order->order_id,
                        'email' => $user->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send success email', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Dispatch notification jobs
                try {
                    \App\Jobs\SendOrderNotificationJob::dispatch($order->order_id);
                    \App\Jobs\OrderPlacedEmailJob::dispatch($order);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch order notification jobs', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Payment success processed via job', [
                'order_id' => $order->order_id,
                'payment_id' => $paymentId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment success handling failed in job: ' . $e->getMessage(), [
                'order_id' => $order->order_id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
