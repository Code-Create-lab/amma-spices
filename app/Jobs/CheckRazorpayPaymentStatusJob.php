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
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckRazorpayPaymentStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;
    protected $paymentId;

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
    public function __construct($orderId, $paymentId)
    {
        $this->orderId = $orderId;
        $this->paymentId = $paymentId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $order = Orders::find($this->orderId);

            if (!$order) {
                Log::error('Order not found in payment status check job', [
                    'order_id' => $this->orderId
                ]);
                return;
            }

            //  && in_array(strtolower($order->payment_status), ['paid', 'success', 'failed'])
            // Skip if already processed
            // if (in_array(strtolower($order->payment->payment_status), ['paid', 'success', 'failed'])) {
            //     Log::info('Order already processed, skipping payment check', [
            //         'order_id' => $order->order_id,
            //         'payment_status' => $order->payment->payment_status,
            //         'order_payment_status' => $order->payment_status
            //     ]);
            //     return;
            // }

            // Fetch payment status from Razorpay
            $razorpayService = new RazorPayService();
            $razorpayResponse = $razorpayService->getPayment($this->paymentId);

            if (!$razorpayResponse || !isset($razorpayResponse['status'])) {
                Log::error('Invalid Razorpay response in payment check job', [
                    'order_id' => $this->orderId,
                    'payment_id' => $this->paymentId,
                    'response' => $razorpayResponse
                ]);

                $order->update([
                    'payment_status' => 'failed',
                    'order_status'   => 'failed',
                ]);
                return;
            }

            $paymentStatus = $razorpayResponse['status'];

            Log::info('Payment status checked from Razorpay', [
                'order_id' => $order->order_id,
                'payment_id' => $this->paymentId,
                'status' => $paymentStatus
            ]);

            // Handle based on payment status
            if (in_array($paymentStatus, ['authorized', 'captured'])) {
                $this->handlePaymentSuccess($order, $razorpayResponse);
            } elseif (in_array($paymentStatus, ['failed'])) {
                $this->handlePaymentFailure($order, $razorpayResponse);
            } else {
                Log::info('Payment status is pending or unknown', [
                    'order_id' => $order->order_id,
                    'status' => $paymentStatus
                ]);
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
    private function handlePaymentSuccess($order, $payment)
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
            $paymentMethod = $payment['method'] ?? 'razorpay';

            // Insert/Update payment record
            DB::table('order_payments')->updateOrInsert(
                ['payment_id' => $this->paymentId, 'order_id' => $order->order_id],
                [
                    'order_id'              => $order->order_id,
                    'payment_id'            => $this->paymentId,
                    'mode'                  => 'razorpay',
                    'transaction_number'    => $order->razorpay_order_id,
                    'transaction_date'      => now(),
                    'method'                => $paymentMethod,
                    'currency'              => $payment['currency'] ?? 'INR',
                    'amount'                => ($payment['amount'] ?? 0) / 100,
                    'payment_status'        => 'Paid',
                    'provider_reference_id' => $providerReference,
                    'json_response'         => json_encode($payment),
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
                'payment_id' => $this->paymentId
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
     * Handle payment failure
     */
    private function handlePaymentFailure($order, $payment)
    {
        try {
            DB::beginTransaction();

            // Update order
            $order->update([
                'payment_status' => 'failed',
                'order_status'   => 'failed',
            ]);

            // Log failed payment
            $providerReference = 'BTX-' . strtoupper(Str::random(12));
            $paymentMethod = $payment['method'] ?? 'razorpay';

            DB::table('order_payments')->updateOrInsert(
                ['payment_id' => $this->paymentId],
                [
                    'order_id'              => $order->order_id,
                    'payment_id'            => $this->paymentId,
                    'mode'                  => 'razorpay',
                    'transaction_number'    => $order->razorpay_order_id,
                    'transaction_date'      => now(),
                    'method'                => $paymentMethod,
                    'currency'              => $payment['currency'] ?? 'INR',
                    'amount'                => ($payment['amount'] ?? 0) / 100,
                    'payment_status'        => 'FAILED',
                    'provider_reference_id' => $providerReference,
                    'json_response'         => json_encode($payment),
                    'transaction_type'      => 'FAILED',
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]
            );

            DB::commit();

            // Send failure email
            $user = User::find($order->user_id);
            if ($user) {
                try {
                    Mail::to($user->email)->send(new PaymentFailed($order));
                    Log::info('Payment failed email sent', [
                        'order_id' => $order->order_id,
                        'email' => $user->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send failure email', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Payment failure processed via job', [
                'order_id' => $order->order_id,
                'payment_id' => $this->paymentId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment failure handling failed in job: ' . $e->getMessage(), [
                'order_id' => $order->order_id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Payment status check job failed permanently', [
            'order_id' => $this->orderId,
            'payment_id' => $this->paymentId,
            'error' => $exception->getMessage()
        ]);
    }
}
