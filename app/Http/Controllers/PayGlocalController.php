<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\StoreOrders;
use App\Models\Variation;
use App\Services\PayGlocalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayGlocalController extends Controller
{
    private $payGlocalService;

    public function __construct(PayGlocalService $payGlocalService)
    {
        $this->payGlocalService = $payGlocalService;
    }

    /**
     * Initiate payment
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'currency' => 'sometimes|string|size:3',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'customer_name' => 'required|string',
        ]);

        try {
            $paymentData = [
                'order_id' => $request->order_id,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'INR',
                'billing_data' => [
                    'firstName' => explode(' ', $request->customer_name)[0] ?? '',
                    'lastName' => explode(' ', $request->customer_name, 2)[1] ?? '',
                    'emailId' => $request->customer_email,
                    'phoneNumber' => $request->customer_phone,
                    'addressStreet1' => $request->address_street1 ?? '',
                    'addressCity' => $request->address_city ?? '',
                    'addressState' => $request->address_state ?? '',
                    'addressPostalCode' => $request->address_postal_code ?? '',
                    'addressCountry' => $request->address_country ?? 'IN',
                ]
            ];

            if ($request->has('risk_data')) {
                $paymentData['risk_data'] = $request->risk_data;
            }

            $response = $this->payGlocalService->initiatePayment($paymentData);

            if ($response['status'] === 'CREATED') {
                // Store transaction in database
                DB::table('payglocal_transactions')->insert([
                    'gid' => $response['gid'],
                    'merchant_txn_id' => $request->order_id,
                    'amount' => $request->amount,
                    'currency' => $request->currency ?? 'INR',
                    'status' => 'CREATED',
                    'redirect_url' => $response['data']['redirectUrl'],
                    'status_url' => $response['data']['statusUrl'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'gid' => $response['gid'],
                    'redirect_url' => $response['data']['redirectUrl'],
                    'message' => 'Payment initiated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed',
                'error' => $response
            ], 400);
        } catch (\Exception $e) {
            Log::error('Payment initiation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment callback
     */
    public function handleCallback(Request $request)
    {
        try {
            $token = $request->input('x-gl-token');

            if (!$token) {
                throw new \Exception('No token received in callback');
            }

            $payload = $this->payGlocalService->verifyCallbackToken($token);

                // dd($payload);
            Log::info('PayGlocal Callback Received:', [
                'gid' => $payload['gid'] ?? 'unknown',
                'status' => $payload['status'] ?? 'unknown',
                'merchantTxnId' => $payload['merchantTxnId'] ?? 'unknown',
                'payload' => $payload
            ]);

            // Update transaction status in payglocal_transactions table (if you have it)
            if (Schema::hasTable('payglocal_transactions')) {
                DB::table('payglocal_transactions')
                    ->where('gid', $payload['gid'])
                    ->update([
                        'status' => $payload['status'],
                        'callback_data' => json_encode($payload),
                        'updated_at' => now(),
                    ]);
            }

            // Find the order using GID or merchant transaction ID
            $order = null;
            // $payment = null;

            // Try to find order by GID in payments table
            // $payment = \App\Models\Payment::where('gid', $payload['gid'])->first();
            // if ($payment) {
            $order = Orders::where('cart_id', $payload['merchantTxnId'])->first();

            if ($order && $order->user_id) {
                Auth::loginUsingId($order->user_id);

                Log::info('User authenticated via PayGlocal callback', [
                    'user_id' => $order->user_id,
                    'order_id' => $order->id
                ]);
            }
            // }
            // dd($payload, $order);
            // If not found, try to find by merchant transaction ID (user phone)
            // if (!$order && isset($payload['merchantTxnId'])) {
            //     // $user = \App\Models\User::where('user_id', $order->user_id)->first();
            //     // if ($user) {
            //     //     $order = \App\Models\Orders::where('user_id', $user->id)
            //     //         ->where('payment_status', 'pending')
            //     //         ->latest()
            //     //         ->first();
            //     // }
            // }

            if (!$order) {
                Log::error('Order not found for callback', [
                    'gid' => $payload['gid'],
                    'merchantTxnId' => $payload['merchantTxnId'] ?? 'not provided'
                ]);

                // dd($order);
                return redirect()->route('payment.error')->with('error', 'Order not found');
            }
            // dd($payload);
            // Handle different payment statuses
            switch (strtoupper($payload['status'])) {
                case 'SENT_FOR_CAPTURE':
                case 'SUCCESS':
                case 'COMPLETED':
                    // Check if already processed to prevent duplicate callbacks
                    if ($order->payment_status === 'paid') {
                        Log::warning('Duplicate payment callback ignored', [
                            'order_id' => $order->order_id,
                            'gid' => $payload['gid'],
                            'status' => 'already_processed'
                        ]);
                        $encryptedId = \Illuminate\Support\Facades\Crypt::encrypt($order->order_id);
                        return redirect()->route('customer.orders.order_success', ['orderId' => $encryptedId]);
                    }

                    // Start transaction for atomic stock update
                    DB::beginTransaction();

                    try {
                        // Update order status
                        $order->update([
                            'payment_status' => 'paid',
                            'order_status' => 'Pending',
                            'paid_at' => now()
                        ]);

                    // Create comprehensive payment record
                    $paymentData = [
                        'order_id' => $order->order_id,
                        'payment_gateway' => 'payglocal',
                        'gid' => $payload['gid'] ?? null,
                        'merchant_txn_id' => $payload['merchantTxnId'] ?? null,
                        'merchant_unique_id' => $payload['merchantUniqueId'] ?? null,
                        'amount' => $payload['amount'] ?? $order->total_price,
                        'currency' => $payload['currency'] ?? 'INR',
                        'payment_status' => strtoupper($payload['status']),
                        'payment_method' => $payload['paymentMethod'] ?? 'Online',
                        'gateway_response' => $payload,
                        'billing_data' => isset($payload['billingData']) ? $payload['billingData'] : null,
                        'transaction_reference' => $payload['transactionReference'] ?? null,
                        'bank_reference' => $payload['bankReference'] ?? null,
                        'payment_date' => now(),
                        'processed_at' => now(),
                        'failure_reason' => null,
                        'retry_count' => 0,
                        'is_refunded' => false,
                        'refunded_amount' => 0.00,
                        'notes' => 'Payment successful via PayGlocal callback'
                    ];

                    // Update or create payment record
                    $payment = \App\Models\Payment::updateOrCreate(
                        ['gid' => $payload['gid']],
                        $paymentData
                    );

                        // Send email notifications
                        try {
                            $checkoutComponent = new \App\Http\Livewire\Checkout();
                            $emailResult = $checkoutComponent->sendOrderSuccessEmail($order->order_id);
                            Log::info('Email notification result:', $emailResult);
                        } catch (\Exception $e) {
                            Log::error('Failed to send email notification:', [
                                'order_id' => $order->id,
                                'error' => $e->getMessage()
                            ]);
                        }

                        // FIX: Decrement stock for ALL order items, not just the first one
                        $storeOrders = StoreOrders::where('order_cart_id', $order->cart_id)->get();
                        $stockUpdateFailed = false;
                        $failedItems = [];

                        foreach ($storeOrders as $storeOrder) {
                            // Use atomic decrement with stock validation to prevent negative stock
                            $updated = Variation::where('id', $storeOrder->varient_id)
                                ->where('stock', '>=', $storeOrder->quantity)
                                ->decrement('stock', $storeOrder->quantity);

                            if (!$updated) {
                                $stockUpdateFailed = true;
                                $failedItems[] = [
                                    'variation_id' => $storeOrder->varient_id,
                                    'product_name' => $storeOrder->product_name,
                                    'quantity' => $storeOrder->quantity
                                ];
                                Log::error('Insufficient stock for variation', [
                                    'variation_id' => $storeOrder->varient_id,
                                    'product_name' => $storeOrder->product_name,
                                    'requested_quantity' => $storeOrder->quantity,
                                    'order_id' => $order->order_id
                                ]);
                            }
                        }

                        // If any stock update failed, rollback and handle error
                        if ($stockUpdateFailed) {
                            DB::rollBack();
                            Log::error('Payment callback - stock update failed', [
                                'order_id' => $order->order_id,
                                'failed_items' => $failedItems
                            ]);

                            // Update order to reflect stock issue
                            $order->update([
                                'order_status' => 'failed',
                                // 'note' => 'Payment successful but insufficient stock. Admin action required.'
                            ]);

                            return redirect()->route('payment.error')->with('error', 'Payment successful but items are out of stock. Our team will contact you shortly.');
                        }

                        // Clear user's cart after successful stock update
                        Cart::where('user_id', Auth::id())->delete();

                        DB::commit();

                        Log::info('Payment Success Processed:', [
                            'order_id' => $order->order_id,
                            'payment_id' => $payment->id,
                            'gid' => $payload['gid'],
                            'amount' => $payment->amount,
                            'stock_updated' => true
                        ]);

                        // Redirect to success page with encrypted order ID
                        $encryptedId = \Illuminate\Support\Facades\Crypt::encrypt($order->order_id);
                        return redirect()->route('customer.orders.order_success', ['orderId' => $encryptedId]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Payment callback transaction failed:', [
                            'order_id' => $order->order_id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        return redirect()->route('payment.error')->with('error', 'Payment processing error. Please contact support.');
                    }


                case 'CUSTOMER_CANCELLED':
                    // Update order status for pending payments
                    $order->update([
                        'payment_status' => strtoupper($payload['status']),
                        'order_status' => 'failed'
                    ]);

                    // dd($order->orderItems);
                    // Create payment record for pending payment
                    $pendingPaymentData = [
                        'order_id' => $order->order_id,
                        'payment_gateway' => 'payglocal',
                        'gid' => $payload['gid'] ?? null,
                        'merchant_txn_id' => $payload['merchantTxnId'] ?? null,
                        'merchant_unique_id' => $payload['merchantUniqueId'] ?? null,
                        'amount' => $payload['amount'] ?? $order->total_price,
                        'currency' => $payload['currency'] ?? 'INR',
                        'payment_status' => strtoupper($payload['status']),
                        'payment_method' => $payload['paymentMethod'] ?? 'Online',
                        'gateway_response' => $payload,
                        'billing_data' => isset($payload['billingData']) ? $payload['billingData'] : null,
                        'payment_date' => now(),
                        'processed_at' => now(),
                        'failure_reason' => null,
                        'retry_count' => 0,
                        'is_refunded' => false,
                        'refunded_amount' => 0.00,
                        'notes' => 'Cancelled by User'
                    ];

                    // Update or create payment record for pending payment
                    \App\Models\Payment::updateOrCreate(
                        ['gid' => $payload['gid']],
                        $pendingPaymentData
                    );

                    Log::info('Payment Pending:', [
                        'order_id' => $order->order_id,
                        'gid' => $payload['gid'],
                        'status' => $payload['status'],
                        'payload' => $payload,
                    ]);

                    // Send email notifications
                    // try {
                    //     $checkoutComponent = new \App\Http\Livewire\Checkout();
                    //     $emailResult = $checkoutComponent->sendOrderSuccessEmail($order->order_id);
                    //     Log::info('Email notification result:', $emailResult);
                    // } catch (\Exception $e) {
                    //     Log::error('Failed to send email notification:', [
                    //         'order_id' => $order->id,
                    //         'error' => $e->getMessage()
                    //     ]);
                    // }

                    // $storeOrder = StoreOrders::where('order_cart_id', $order->cart_id)->first();
                    // // dd($payload, "CUSTOMER_CANCELLED");
                    // $productVarientId = $storeOrder->varient_id;
                    // $productOrderQty = $storeOrder->quantity;

                    // Variation::where('id', $productVarientId)->increment('stock', $productOrderQty);
                    // $order->delete();
                    // Redirect to success page with encrypted order ID
                    $encryptedId = \Illuminate\Support\Facades\Crypt::encrypt($order->order_id);
                    return redirect()->route('payment.customer-cancelled')->with('error', 'Cancelled by Customer');
                case 'AUTHENTICATION_TIMEOUT':
                case 'ISSUER_DECLINE':
                case 'GENERAL_DECLINE':
                case 'FAILED':
                case 'FAILURE':
                     // Update order status for failed payments
                    $order->update([
                        'payment_status' => strtoupper($payload['status']),
                        'order_status' => 'failed'
                    ]);

                    // foreach ($order->orderItems as $item) {

                    //     // $getVariation = Variation::where('id', $item->varient_id)->first();
                    //     // dd($item);
                    //     // Cart::create([
                    //     //     'product_id' => $getVariation->product_id,
                    //     //     'variation_id' => $item->varient_id,
                    //     //     'user_id' => auth()->user()->id,
                    //     //     'quantity' => $item->quantity,
                    //     // ]);
                    // }
                    // Create payment record for failed payment
                    $failedPaymentData = [
                        'order_id' => $order->order_id,
                        'payment_gateway' => 'payglocal',
                        'gid' => $payload['gid'] ?? null,
                        'merchant_txn_id' => $payload['merchantTxnId'] ?? null,
                        'merchant_unique_id' => $payload['merchantUniqueId'] ?? null,
                        'amount' => $payload['amount'] ?? $order->total_price,
                        'currency' => $payload['currency'] ?? 'INR',
                        'payment_status' => strtoupper($payload['status']),
                        'payment_method' => $payload['paymentMethod'] ?? 'Online',
                        'gateway_response' => $payload,
                        'billing_data' => isset($payload['billingData']) ? $payload['billingData'] : null,
                        'payment_date' => now(),
                        'processed_at' => now(),
                        'failure_reason' => $payload['failureReason'] ?? $payload['status'],
                        'retry_count' => ($payment->retry_count ?? 0) + 1,
                        'is_refunded' => false,
                        'refunded_amount' => 0.00,
                        'notes' => 'Payment failed via PayGlocal callback: ' . ($payload['failureReason'] ?? $payload['status'])
                    ];

                    // Update or create payment record for failed payment
                    \App\Models\Payment::updateOrCreate(
                        ['gid' => $payload['gid']],
                        $failedPaymentData
                    );


                    // Send email notifications
                    try {
                        $checkoutComponent = new \App\Http\Livewire\Checkout();
                        $emailResult = $checkoutComponent->sendOrderSuccessEmail($order->order_id);
                        Log::info('Email notification result:', $emailResult);
                    } catch (\Exception $e) {
                        Log::error('Failed to send email notification:', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    //  $component = new Checkout();
                    //  $component->successOrder($order->cart_id);

                    Log::warning('Payment Failed:', [
                        'order_id' => $order->id,
                        'gid' => $payload['gid'],
                        'status' => $payload['status'],
                        'reason' => $payload['failureReason'] ?? 'Unknown'
                    ]);

                    if ($order && $order->user_id) {
                        Auth::loginUsingId($order->user_id);

                        Log::info('User authenticated via PayGlocal callback', [
                            'user_id' => $order->user_id,
                            'order_id' => $order->id
                        ]);
                    }
                    // $storeOrder = StoreOrders::where('order_cart_id', $order->cart_id)->first();
                    // // dd($payload, "CUSTOMER_CANCELLED");
                    // $productVarientId = $storeOrder->varient_id;
                    // $productOrderQty = $storeOrder->quantity;

                    // Variation::where('id', $productVarientId)->increment('stock', $productOrderQty);
                    // $order->delete();
                    // dd($updateVariation, $storeOrder, $payload);
                    // Redirect to failure page
                    return redirect()->route('payment.failed', ['gid' => $payload['gid']]);

                case 'PENDING':
                case 'ABANDONED':
                       // Update order status for failed payments
                    $order->update([
                        'payment_status' => strtoupper($payload['status']),
                        'order_status' => 'failed'
                    ]);

                    // foreach ($order->orderItems as $item) {

                    //     // $getVariation = Variation::where('id', $item->varient_id)->first();
                    //     // dd($item);
                    //     // Cart::create([
                    //     //     'product_id' => $getVariation->product_id,
                    //     //     'variation_id' => $item->varient_id,
                    //     //     'user_id' => auth()->user()->id,
                    //     //     'quantity' => $item->quantity,
                    //     // ]);
                    // }
                    // Create payment record for failed payment
                    $failedPaymentData = [
                        'order_id' => $order->order_id,
                        'payment_gateway' => 'payglocal',
                        'gid' => $payload['gid'] ?? null,
                        'merchant_txn_id' => $payload['merchantTxnId'] ?? null,
                        'merchant_unique_id' => $payload['merchantUniqueId'] ?? null,
                        'amount' => $payload['amount'] ?? $order->total_price,
                        'currency' => $payload['currency'] ?? 'INR',
                        'payment_status' => strtoupper($payload['status']),
                        'payment_method' => $payload['paymentMethod'] ?? 'Online',
                        'gateway_response' => $payload,
                        'billing_data' => isset($payload['billingData']) ? $payload['billingData'] : null,
                        'payment_date' => now(),
                        'processed_at' => now(),
                        'failure_reason' => $payload['failureReason'] ?? $payload['status'],
                        'retry_count' => ($payment->retry_count ?? 0) + 1,
                        'is_refunded' => false,
                        'refunded_amount' => 0.00,
                        'notes' => 'Payment failed via PayGlocal callback: ' . ($payload['failureReason'] ?? $payload['status'])
                    ];

                    // Update or create payment record for failed payment
                    \App\Models\Payment::updateOrCreate(
                        ['gid' => $payload['gid']],
                        $failedPaymentData
                    );


                    // Send email notifications
                    try {
                        $checkoutComponent = new \App\Http\Livewire\Checkout();
                        $emailResult = $checkoutComponent->sendOrderSuccessEmail($order->order_id);
                        Log::info('Email notification result:', $emailResult);
                    } catch (\Exception $e) {
                        Log::error('Failed to send email notification:', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    //  $component = new Checkout();
                    //  $component->successOrder($order->cart_id);

                    Log::warning('Payment Failed:', [
                        'order_id' => $order->id,
                        'gid' => $payload['gid'],
                        'status' => $payload['status'],
                        'reason' => $payload['failureReason'] ?? 'Unknown'
                    ]);

                    if ($order && $order->user_id) {
                        Auth::loginUsingId($order->user_id);

                        Log::info('User authenticated via PayGlocal callback', [
                            'user_id' => $order->user_id,
                            'order_id' => $order->id
                        ]);
                    }
                    // $storeOrder = StoreOrders::where('order_cart_id', $order->cart_id)->first();
                    // // dd($payload, "CUSTOMER_CANCELLED");
                    // $productVarientId = $storeOrder->varient_id;
                    // $productOrderQty = $storeOrder->quantity;

                    // Variation::where('id', $productVarientId)->increment('stock', $productOrderQty);
                    // $order->delete();
                    // dd($updateVariation, $storeOrder, $payload);
                    // Redirect to failure page
                    return redirect()->route('payment.failed', ['gid' => $payload['gid']]);
                case 'PROCESSING':
                default:
                    // Update order status for pending payments
                    $order->update([
                        'payment_status' => 'failed',
                        'order_status' => 'failed'
                    ]);

                    foreach ($order->orderItems as $item) {

                        // $getVariation = Variation::where('id', $item->varient_id)->first();
                        // dd($item);
                        // Cart::create([
                        //     'product_id' => $getVariation->product_id,
                        //     'variation_id' => $item->varient_id,
                        //     'user_id' => auth()->user()->id,
                        //     'quantity' => $item->quantity,
                        // ]);
                    }

                    // Create payment record for pending payment
                    $pendingPaymentData = [
                        'order_id' => $order->order_id,
                        'payment_gateway' => 'payglocal',
                        'gid' => $payload['gid'] ?? null,
                        'merchant_txn_id' => $payload['merchantTxnId'] ?? null,
                        'merchant_unique_id' => $payload['merchantUniqueId'] ?? null,
                        'amount' => $payload['amount'] ?? $order->total_price,
                        'currency' => $payload['currency'] ?? 'INR',
                        'payment_status' => strtoupper($payload['status']),
                        'payment_method' => $payload['paymentMethod'] ?? 'Online',
                        'gateway_response' => $payload,
                        'billing_data' => isset($payload['billingData']) ? $payload['billingData'] : null,
                        'payment_date' => now(),
                        'processed_at' => now(),
                        'failure_reason' => null,
                        'retry_count' => 0,
                        'is_refunded' => false,
                        'refunded_amount' => 0.00,
                        'notes' => 'Payment pending via PayGlocal callback'
                    ];

                    // Update or create payment record for pending payment
                    \App\Models\Payment::updateOrCreate(
                        ['gid' => $payload['gid']],
                        $pendingPaymentData
                    );

                    Log::info('Payment Pending:', [
                        'order_id' => $order->order_id,
                        'gid' => $payload['gid'],
                        'status' => $payload['status'],
                        'payload' => $payload,
                    ]);

                    // Send email notifications
                    try {
                        $checkoutComponent = new \App\Http\Livewire\Checkout();
                        $emailResult = $checkoutComponent->sendOrderSuccessEmail($order->order_id);
                        Log::info('Email notification result:', $emailResult);
                    } catch (\Exception $e) {
                        Log::error('Failed to send email notification:', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    // $storeOrder = StoreOrders::where('order_cart_id', $order->cart_id)->first();
                    // // dd($payload, "CUSTOMER_CANCELLED");
                    // $productVarientId = $storeOrder->varient_id;
                    // $productOrderQty = $storeOrder->quantity;

                    // Variation::where('id', $productVarientId)->increment('stock', $productOrderQty);
                    // $order->delete();
                    // Redirect to success page with encrypted order ID
                    $encryptedId = \Illuminate\Support\Facades\Crypt::encrypt($order->order_id);
                    return redirect()->route('payment.error')->with('error', 'Order not found');
                    // return redirect()->route('customer.orders.order_success', ['orderId' => $encryptedId]);

                    // // Redirect to pending page
                    // return redirect()->route('payment.pending', ['gid' => $payload['gid']]);
            }
        } catch (\Exception $e) {
            Log::error('Callback handling failed:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            // dd($e->getMessage());
            return redirect()->route('payment.error')->with('error', 'Payment verification failed');
        }
    }
    /**
     * Check payment status
     */
    public function checkStatus($identifier)
    {
        try {
            $response = $this->payGlocalService->getPaymentStatus($identifier);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Status check failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Status check failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process refund
     */
    public function processRefund(Request $request)
    {
        $request->validate([
            'gid' => 'required|string',
            'order_id' => 'required|string',
            'type' => 'required|in:F,P',
            'amount' => 'required_if:type,P|numeric|min:0.01',
            'currency' => 'sometimes|string|size:3',
        ]);

        try {
            $refundData = [
                'order_id' => $request->order_id,
                'type' => "P",
                'currency' => $request->currency ?? 'INR',
            ];

            if ($request->type === 'P') {
                $refundData['amount'] = $request->amount;
            }
            
            $response = $this->payGlocalService->processRefund($request->gid, $refundData);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Refund processed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Refund processing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Refund processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle successful payment post-processing
     */
    private function handleSuccessfulPayment(Orders $order, Payment $payment)
    {
        try {
            // 1. Send order success email using the new Checkout method
            $checkoutComponent = new \App\Http\Livewire\Checkout();
            $emailResult = $checkoutComponent->sendOrderSuccessEmail($order->id);

            Log::info('Email notification result:', $emailResult);

            // // 2. Update inventory
            // $this->updateInventory($order);

            // // 3. Generate invoice
            // $this->generateInvoice($order);

            // // 4. Send SMS notification (already handled in sendOrderSuccessEmail method)

            // 5. Trigger any webhooks to third-party services
            $this->triggerWebhooks($order, 'payment_success');

            Log::info('Post-payment processing completed for order: ' . $order->id);
        } catch (\Exception $e) {
            Log::error('Post-payment processing error:', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
