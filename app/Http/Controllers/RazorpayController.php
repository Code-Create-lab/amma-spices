<?php

namespace App\Http\Controllers;

use App\Jobs\OrderPlacedEmailJob;
use App\Jobs\SendOrderNotificationJob;
use Illuminate\Http\Request;
use App\Services\RazorPayService;
use App\Models\Orders;
use App\Models\StoreOrders;
use App\Models\Variation;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use App\Mail\PaymentSuccess;
use App\Mail\PaymentFailed;
use Illuminate\Support\Facades\Mail;

class RazorpayController extends Controller
{
    private $razorpayService;

    public function __construct(RazorPayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    /**
     * Show checkout page with Razorpay order
     */
    public function show(Request $request, $cart_id)
    {
        Log::info('payment gateway called');
        $order = Orders::where('cart_id', $cart_id)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
                'code'    => 404
            ]);
        }

        if (strtolower($order->payment_status) === 'success' || strtolower($order->payment_status) === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This order is already paid.',
                'code'    => 422
            ]);
        }

        // Create Razorpay order
        $response = $this->razorpayService->createOrder([
            'order_id' => $cart_id,
            'amount'   => $order->total_price,
            'currency' => 'INR',
            'receipt'  => $cart_id,
        ]);

        $razorpayOrderId = $response['order']->id ?? null;

        if (!$razorpayOrderId) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Razorpay order.',
                'code'    => 500
            ]);
        }

        Log::info('order created in razorpay');

        $order->razorpay_order_id = $razorpayOrderId;
        $order->save();

        return view('payment.checkout', [
            'order'           => $order,
            'razorpayOrderId' => $razorpayOrderId,
            'razorpayKey'     => config('services.razorpay.key'),
        ]);
    }

    /**
     * Handle Razorpay webhook (SERVER-TO-SERVER)
     * This ensures payment updates even if user's page crashes
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Get webhook signature and body
            $webhookSignature = $request->header('X-Razorpay-Signature');
            $webhookBody = $request->getContent();

            // Verify webhook signature
            $verification = $this->razorpayService->verifyWebhookSignature($webhookBody, $webhookSignature);

            if (!$verification['success']) {
                Log::error('Webhook signature verification failed', [
                    'error' => $verification['error'] ?? 'Unknown error'
                ]);
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $payload = $request->all();
            $event = $payload['event'] ?? null;

            Log::info('Webhook received', [
                'event' => $event,
                'payload' => $payload
            ]);

            // Handle different webhook events
            switch ($event) {
                case 'payment.authorized':
                case 'payment.captured':
                    $this->handleWebhookPaymentSuccess($payload['payload']['payment']['entity'] ?? []);
                    break;

                case 'payment.failed':
                    $this->handleWebhookPaymentFailure($payload['payload']['payment']['entity'] ?? []);
                    break;

                default:
                    Log::info('Unhandled webhook event: ' . $event);
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle webhook payment success
     */
    private function handleWebhookPaymentSuccess($payment)
    {
        try {
            $razorpayOrderId = $payment['order_id'] ?? null;
            $paymentId = $payment['id'] ?? null;

            if (!$razorpayOrderId || !$paymentId) {
                Log::error('Missing order_id or payment_id in webhook payload', ['payment' => $payment]);
                return;
            }

            // Find order by razorpay_order_id
            $order = Orders::where('razorpay_order_id', $razorpayOrderId)->first();

            if (!$order) {
                Log::error('Order not found for webhook', [
                    'razorpay_order_id' => $razorpayOrderId
                ]);
                return;
            }

            // Check if already processed (idempotency)
            if (in_array(strtolower($order->payment_status), ['paid', 'success'])) {
                Log::info('Order already processed via webhook', [
                    'order_id' => $order->order_id,
                    'payment_status' => $order->payment_status
                ]);
                return;
            }

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

            // Insert payment record
            DB::table('order_payments')->updateOrInsert(
                ['payment_id' => $paymentId],
                [
                    'order_id'              => $order->order_id,
                    'payment_id'            => $paymentId,
                    'mode'                  => 'razorpay',
                    'transaction_number'    => $razorpayOrderId,
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

            // Clear user cart currently disabled, according to flow cart should not be empty on webhook
            Cart::where('user_id', $order->user_id)->delete();

            DB::commit();

            // Send success email
            $user = User::find($order->user_id);
            if ($user) {
                try {
                    if ($order->address->receiver_email !=  $user->email) {

                        Mail::to([$order->address->receiver_email, $user->email])->send(new PaymentSuccess($order));
                    } else {

                        Mail::to([$order->address->receiver_email])->send(new PaymentSuccess($order));
                    }
                    Log::info('Payment success email sent via webhook', [
                        'order_id' => $order->order_id,
                        'email' => $user->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send success email via webhook', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Send order success email (your custom component)
                try {
                    SendOrderNotificationJob::dispatch($order->order_id);
                    OrderPlacedEmailJob::dispatch($order);
                    // $checkoutComponent = new \App\Http\Livewire\Checkout();
                    // $checkoutComponent->sendOrderSuccessEmail($order->order_id);
                } catch (\Exception $e) {
                    Log::error('Failed to send order success email via webhook', [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Webhook: Payment success processed', [
                'order_id' => $order->order_id,
                'payment_id' => $paymentId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Webhook payment success handling failed: ' . $e->getMessage(), [
                'payment' => $payment ?? [],
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle webhook payment failure
     */
    private function handleWebhookPaymentFailure($payment)
    {
        try {
            $razorpayOrderId = $payment['order_id'] ?? null;
            $paymentId = $payment['id'] ?? null;

            if (!$razorpayOrderId) {
                Log::error('Missing order_id in webhook failure payload', ['payment' => $payment]);
                return;
            }

            $order = Orders::where('razorpay_order_id', $razorpayOrderId)->first();

            if (!$order) {
                Log::error('Order not found for webhook failure', [
                    'razorpay_order_id' => $razorpayOrderId
                ]);
                return;
            }

            // Check if already processed as failed
            if (strtolower($order->payment_status) === 'failed') {
                Log::info('Order already marked as failed', [
                    'order_id' => $order->order_id
                ]);
                return;
            }

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
                ['payment_id' => $paymentId],
                [
                    'order_id'              => $order->order_id,
                    'payment_id'            => $paymentId,
                    'mode'                  => 'razorpay',
                    'transaction_number'    => $razorpayOrderId,
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
                    Log::info('Payment failed email sent via webhook', [
                        'order_id' => $order->order_id,
                        'email' => $user->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send failure email via webhook', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Webhook: Payment failure processed', [
                'order_id' => $order->order_id,
                'payment_id' => $paymentId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Webhook payment failure handling failed: ' . $e->getMessage(), [
                'payment' => $payment ?? [],
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle webhook payment failure
     */
    public function handlePaymentCancel(Request $request)
    {
        try {
            $cart_id = $request->cart_id;

            $order = Orders::where('cart_id', $cart_id)->first();

            if (!$order) {
                Log::error('Order not found for webhook failure', [
                    'cart_id' => $cart_id
                ]);
                return;
            }

            // Check if already processed as failed
            if (strtolower($order->payment_status) === 'failed') {
                Log::info('Order already marked as failed', [
                    'order_id' => $order->order_id
                ]);
                return;
            }

            DB::beginTransaction();

            // Update order
            $order->update([
                'payment_status' => 'failed',
                'order_status'   => 'failed',
            ]);

            // Log failed payment
            $providerReference = 'BTX-' . strtoupper(Str::random(12));
            $paymentMethod = 'razorpay';
            $paymentId = 'abandoned_' . $order->order_id . '_' . time();

            DB::table('order_payments')->updateOrInsert(
                ['payment_id' => $paymentId],
                [
                    'order_id'              => $order->order_id,
                    'payment_id'            => $paymentId,
                    'mode'                  => 'razorpay',
                    'transaction_number'    => $order->razorpay_order_id,
                    'transaction_date'      => now(),
                    'method'                => $paymentMethod,
                    'currency'              => 'INR',
                    'amount'                => $order->total_price,
                    'payment_status'        => 'FAILED',
                    'provider_reference_id' => $providerReference,
                    'json_response'         => json_encode([
                        'status' => 'abandoned',
                        'reason' => 'Payment not completed - No payment data available',
                        'order_id' => $order->order_id,
                        'razorpay_order_id' => $order->razorpay_order_id ?? null,
                        'order_date' => $order->order_date,
                        'marked_failed_at' => now(),
                    ]),
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
                    Log::info('Manual Payment failed email sent ', [
                        'order_id' => $order->order_id,
                        'email' => $user->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send manual payment failure email', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Manual: Payment failure processed', [
                'order_id' => $order->order_id,
                'payment_id' => $paymentId
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Webhook payment failure handling failed: ' . $e->getMessage(), [
                'payment' => $payment ?? [],
                'trace' => $e->getTraceAsString()
            ]);
        }
         return response()->json(['success' => true]);
    }

    /**
     * Handle Razorpay callback (BROWSER CALLBACK)
     */
    public function handleCallback(Request $request)
    {
        Log::info('payment gateway callback function called');
        try {
            $verification = $this->razorpayService->verifyPayment($request->all());
            $razorpay_order_id = $request->razorpay_order_id ?? null;

            if (!$verification['success']) {
                return redirect()->route('payment.error', ['cart_id' => $razorpay_order_id])
                    ->with('error', 'Payment verification failed');
            }

            $payload   = $verification['payload'];
            $paymentId = $request->razorpay_payment_id;
            $api       = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

            // Fetch payment details from Razorpay
            $paymentDetails = $api->payment->fetch($paymentId);
            $paymentMethod  = $paymentDetails->method ?? 'razorpay';
            $status         = $paymentDetails->status ?? 'created';

            Log::info('payment details fetched',['details' => $paymentDetails]);


            // Find order using razorpay_order_id
            $order = Orders::where('razorpay_order_id', $razorpay_order_id)->first();

            if (!$order) {
                Log::error('Order not found for Razorpay callback', ['payload' => $payload]);
                return redirect()->route('payment.error', ['cart_id' => $razorpay_order_id])
                    ->with('error', 'Order not found');
            }

            // Check if already processed by webhook
            if (in_array(strtolower($order->payment_status), ['paid', 'success'])) {
                Log::info('Order already processed (likely by webhook)', [
                    'order_id' => $order->order_id,
                    'payment_status' => $order->payment_status
                ]);

                Auth::loginUsingId($order->user_id);
                $encryptedId = Crypt::encrypt($order->order_id);
                return redirect()->route('customer.orders.order_success', ['orderId' => $encryptedId]);
            }

            Log::info('Processing payment callback', [
                'payload' => $payload,
                'status' => $status,
                'order_id' => $order->order_id
            ]);

            // Generate provider reference id
            $providerReference = 'BTX-' . strtoupper(Str::random(12));

            DB::beginTransaction();

            switch (strtolower($status)) {
                case 'captured':
                    $order->update([
                        'payment_status' => 'paid',
                        'order_status'   => 'Pending',
                        'paid_at'        => now(),
                    ]);

                    DB::table('order_payments')->updateOrInsert(
                        ['payment_id' => $paymentId],
                        [
                            'order_id'              => $order->order_id,
                            'payment_id'            => $paymentId,
                            'mode'                  => 'razorpay',
                            'transaction_number'    => $razorpay_order_id,
                            'transaction_date'      => now(),
                            'method'                => $paymentMethod,
                            'currency'              => $payload['currency'] ?? 'INR',
                            'amount'                => ($payload['amount'] ?? ($order->total_price)),
                            'payment_status'        => 'Paid',
                            'provider_reference_id' => $providerReference,
                            'json_response'         => json_encode($payload),
                            'transaction_type'      => 'CAPTURED',
                            'created_at'            => now(),
                            'updated_at'            => now(),
                        ]
                    );

                    $user = User::find($order->user_id);

                    try{
                        if ($order->address->receiver_email !=  $user->email) {

                            Mail::to([$order->address->receiver_email, $user->email])->send(new PaymentSuccess($order));
                        } else {

                            Mail::to([$order->address->receiver_email])->send(new PaymentSuccess($order));
                        }

                        Log::info('Payment success email sent via webhook', [
                            'order_id' => $order->order_id,
                            'email' => $user->email
                        ]);
                    }
                    catch (\Exception $e) {
                        Log::error('Failed to send email notification:', [
                            'order_id' => $order->order_id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    // Send email notifications
                    try {
                        SendOrderNotificationJob::dispatch($order->order_id);
                        OrderPlacedEmailJob::dispatch($order);
                        // $checkoutComponent = new \App\Http\Livewire\Checkout();
                        // $emailResult = $checkoutComponent->sendOrderSuccessEmail($order->order_id);
                        // Log::info('Email notification result:', $emailResult);
                    } catch (\Exception $e) {
                        Log::error('Failed to send email notification:', [
                            'order_id' => $order->order_id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    $storeOrder = StoreOrders::where('order_cart_id', $order->cart_id)->first();
                    $productVarientId = $storeOrder->varient_id;
                    $productOrderQty = $storeOrder->quantity;

                    Variation::where('id', $productVarientId)->decrement('stock', $productOrderQty);


                    Log::info('Payment Success Processed:', [
                        'order_id' => $order->order_id,
                        'payment_id' => $paymentId,
                        'razorpay_order_id' => $razorpay_order_id,
                        'amount' => ($payload['amount'] ?? ($order->total_price))
                    ]);

                    Auth::loginUsingId($order->user_id);
                    Cart::where('user_id', Auth::id() ?? $order->user_id)->delete();
                    $encryptedId = Crypt::encrypt($order->order_id);
                    DB::commit();

                    return redirect()->route('customer.orders.order_success', ['orderId' => $encryptedId]);

                case 'failed':
                    $order->update([
                        'payment_status' => 'failed',
                        'order_status'   => 'failed',
                    ]);

                    DB::commit();
                    return redirect()->route('payment.failed', ['gid' => $order->cart_id])
                        ->with('error', 'Payment failed or cancelled.');

                case 'created':
                default:
                    $order->update([
                        'payment_status' => 'pending',
                        'order_status'   => 'pending',
                    ]);

                    DB::table('order_payments')->updateOrInsert(
                        ['payment_id' => $paymentId],
                        [
                            'order_id'              => $order->order_id,
                            'payment_id'            => $paymentId,
                            'mode'                  => 'razorpay',
                            'transaction_number'    => $razorpay_order_id,
                            'transaction_date'      => now(),
                            'method'                => $paymentMethod,
                            'currency'              => $payload['currency'] ?? 'INR',
                            'amount'                => ($payload['amount'] ?? ($order->total_price)),
                            'payment_status'        => 'PENDING',
                            'provider_reference_id' => $providerReference,
                            'json_response'         => json_encode($payload),
                            'transaction_type'      => 'PENDING',
                            'created_at'            => now(),
                            'updated_at'            => now(),
                        ]
                    );

                    DB::commit();
                    return redirect()->route('payment.error', ['cart_id' => $order->cart_id])
                        ->with('error', 'Payment is pending');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Razorpay callback failed: ' . $e->getMessage(), ['request' => $request->all()]);
            $cartId = $request->razorpay_order_id ?? null;
            return redirect()->route('payment.error', ['cart_id' => $cartId])
                ->with('error', 'Payment processing failed');
        }
    }

    /**
     * Refund payment → inserts into order_refunds
     */
    public function refundPayment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|string',
            'amount'     => 'sometimes|numeric|min:1'
        ]);

        try {
            $response = $this->razorpayService->refundPayment($request->payment_id, [
                'amount' => $request->amount ? $request->amount : null
            ]);

            if (isset($response['success']) && $response['success']) {
                DB::table('order_refunds')->insert([
                    'payment_id'            => $request->payment_id,
                    'refund_amount'         => ($request->amount ?? 0),
                    'refund_status'         => 'SUCCESS',
                    'refund_response'       => json_encode($response),
                    'transaction_reference' => 'RFD-' . strtoupper(Str::random(10)),
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Razorpay refund failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Refund failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
