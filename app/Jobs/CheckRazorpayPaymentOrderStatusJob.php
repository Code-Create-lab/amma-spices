<?php

namespace App\Jobs;

use App\Models\Orders;
use App\Models\User;
use App\Models\Cart;
use App\Models\StoreOrders;
use App\Models\Variation;
use App\Services\RazorPayService;
use App\Mail\PaymentSuccess;
use App\Mail\PaymentFailed;
use App\Models\OrderPayments;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckRazorpayPaymentOrderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;
    protected $razorpayOrderId;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct($orderId, $razorpayOrderId)
    {
        $this->orderId = $orderId;
        $this->razorpayOrderId = $razorpayOrderId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $order = Orders::find($this->orderId);

            if (!$order) {
                Log::error('Order not found in payment order status check job', [
                    'order_id' => $this->orderId
                ]);
                return;
            }


            // Fetch payment status from Razorpay
            $razorpayService = new RazorPayService();
            $payment = $razorpayService->getCapturedPaymentByOrderId($this->razorpayOrderId);

            if ($payment) {
                Log::info('Captured payment found via job', [
                    'order_id' => $this->orderId,
                    'payment_id' => $payment['id'],
                ]);

               
                $this->handlePaymentSuccess($order, $payment['id']);

                return;
            }

            // No payment found → mark as failed
            $payments = $razorpayService->getPaymentsByOrderId($this->razorpayOrderId);

            if (!$payments || count($payments->items) === 0) {
                Log::info('No payments found, marking order failed', [
                    'order_id' => $this->orderId,
                ]);

                $this->markOrderAsFailed($order);
            }
           
        } catch (\Exception $e) {
            Log::error('Payment status check job failed: ' . $e->getMessage(), [
                'order_id' => $this->orderId,
                'payment_id' => $this->paymentId,
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to trigger retry mechanism
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
}
