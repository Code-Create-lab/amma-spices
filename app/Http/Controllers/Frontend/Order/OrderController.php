<?php

namespace App\Http\Controllers\Frontend\Order;

use App\Events\SendOrderPlacedMailEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Address\AddressStoreRequest;
use App\Http\Requests\Frontend\Address\AddressUpdateRequest;
use App\Http\Requests\Frontend\Order\OrderStoreRequest;
use App\Jobs\CheckRazorpayPaymentOrderStatusJob;
use App\Jobs\CheckRazorpayPaymentStatusJob;
use App\Mail\CancelOrder;
use App\Mail\PaymentFailed;
use App\Mail\PaymentSuccess;
use App\Models\Address;
use App\Models\CancelledOrder;
use App\Models\Cart;
use App\Models\City;
use App\Models\OrderPayments;
use App\Models\OrderRefund;
use App\Models\Orders;
use App\Models\Shipment;
use App\Models\Society;
use App\Models\StoreOrders;
use App\Models\User;
use App\Models\Variation;
use App\Services\RazorPayService;
use App\Services\ShiprocketService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Razorpay\Api\Api;

class OrderController extends Controller
{
    private function getGuestOrderAccess(): ?array
    {
        $guestOrderAccess = session('guest_order_access');

        if (!is_array($guestOrderAccess) || empty($guestOrderAccess['user_id'])) {
            return null;
        }

        return $guestOrderAccess;
    }

    private function rememberGuestOrderAccess(Orders $order): void
    {
        if (!$order->is_guest_checkout) {
            session()->forget('guest_order_access');
            return;
        }

        session()->put('guest_order_access', [
            'user_id' => (int) $order->user_id,
            'email' => (string) ($order->address?->receiver_email ?? $order->user?->email ?? ''),
            'last_order_id' => (int) $order->order_id,
        ]);
    }

    public function index(Request $request)
    {
        $guestOrderAccess = $this->getGuestOrderAccess();
        $isGuestOrderView = !Auth::check() && !empty($guestOrderAccess);

        if (!Auth::check() && !$isGuestOrderView) {
            return redirect()->route('customer.login.index');
        }

        $viewerUserId = Auth::id() ?? $guestOrderAccess['user_id'];

        $ordersQuery = Orders::with(['orderItems.variation.variation_attributes.attribute.attribute', 'address'])
            ->where('user_id', $viewerUserId)
            ->where('order_status','!=','failed')
            ->orderBy('order_id', 'desc');

        if ($isGuestOrderView) {
            $ordersQuery->where('is_guest_checkout', true);
        }

        $orders = $ordersQuery->paginate(4);

        // $razorPayOrders = Orders::with([
        //     'payment'
        // ])->where('user_id', Auth::id())->whereHas('payment', function ($q) {
        //     $q->whereIn('payment_status', ['pending', 'created', 'initiated']);
        // })->orderBy('order_id', 'desc')->get();

        $razorPayOrders = collect();

        if (Auth::check()) {
            $razorPayOrders = Orders::with('payment')->where('user_id', Auth::id())
                ->whereIn('payment_status', ['Pending', 'created', 'initiated','pending'])
                ->orderBy('order_id', 'desc')->get();
        }

        $showGuestLoginPrompt = $isGuestOrderView
            ? Orders::where('user_id', $viewerUserId)
                ->where('order_status', '!=', 'failed')
                ->where('is_guest_checkout', false)
                ->exists()
            : false;

        $guestOrderEmail = $guestOrderAccess['email'] ?? null;


        // $cancelOrderCount = Orders::where('user_id', Auth::id())->where('payment_method', "COD")->get();
        // dd($cancelOrderCount);

        foreach ($razorPayOrders as $order) {


            // if ($order->payment) {
            // }
            // Only check if payment exists and status is pending/created
            if (
                $order->payment &&
                in_array(strtolower($order->payment->payment_status), ['pending', 'created', 'initiated'])
            ) {


                // Use cache to prevent checking same order multiple times in short period
                $cacheKey = 'payment_check:' . $order->order_id;

                // Skip if we checked this order in the last 5 minutes
                // if (Cache::has($cacheKey)) {
                //     Log::info('Payment status check skipped (cached)', [
                //         'order_id' => $order->order_id
                //     ]);
                //     continue;
                // }

                try {
                    // Dispatch job to check payment status asynchronously
                    CheckRazorpayPaymentStatusJob::dispatch(
                        $order->order_id,
                        $order->payment->payment_id
                    );

                    // Cache this check for 5 minutes to prevent redundant API calls
                    Cache::put($cacheKey, true, now()->addMinutes(5));

                    Log::info('Payment status check job dispatched', [
                        'order_id' => $order->order_id,
                        'payment_id' => $order->payment->payment_id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch payment check job', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            else{
                Log::info('Payment status check from my order', [
                        'order_id' => $order->order_id,
                        'razorpay_id' => $order->razorpay_order_id
                    ]);
                if (!empty($order->razorpay_order_id)) {
                    $razorpayOrderId = $order->razorpay_order_id;
                    // CheckRazorpayPaymentOrderStatusJob::dispatch(
                    //     $order->order_id,
                    //     $order->razorpay_order_id
                    // );
                    // Fetch payment status from Razorpay
                    $razorpayService = new RazorPayService();
                    $payment = $razorpayService->getCapturedPaymentByOrderId($razorpayOrderId);

                    if ($payment) {
                        Log::info('Captured payment found via job', [
                            'order_id' => $order->order_id,
                            'payment_id' => $payment['id'],
                        ]);

                    
                        $this->handlePaymentSuccess($order, $payment['id']);

                    }

                    // No payment found → mark as failed
                    $payments = $razorpayService->getPaymentsByOrderId($razorpayOrderId);

                    if (!$payments || count($payments->items) === 0) {
                        Log::info('No payments found, marking order failed', [
                            'order_id' => $order->order_id,
                        ]);

                        $this->markOrderAsFailed($order);
                    }
                    Log::info('Payment Order status check job dispatched', [
                        'order_id' => $order->order_id,
                        'razorpay_id' => $order->razorpay_order_id
                    ]);
                }
            }
        }

        return view('frontend.order.index', [
            'orders' => $orders,
            'isGuestOrderView' => $isGuestOrderView,
            'showGuestLoginPrompt' => $showGuestLoginPrompt,
            'guestOrderEmail' => $guestOrderEmail,
        ]);
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

     
    public function getTrackOrder()
    {
        $orders = Orders::with(['orderItems.variation.variation_attributes.attribute.attribute', 'address'])
            ->where('user_id', Auth::id())
            ->where('order_status', 'confirmed')
            ->where('payment_method', 'online')
            ->orderBy('order_id', 'desc')
            ->paginate(4);

        $razorPayOrders = Orders::with([
            'payment'
        ])->where('user_id', Auth::id())->whereHas('payment', function ($q) {
            $q->whereIn('payment_status', ['pending', 'created', 'initiated']);
        })->orderBy('order_id', 'desc')->get();


        // $cancelOrderCount = Orders::where('user_id', Auth::id())->where('payment_method', "COD")->get();
        // dd($cancelOrderCount);

        foreach ($razorPayOrders as $order) {


            // if ($order->payment) {
            // }
            // Only check if payment exists and status is pending/created
            if (
                $order->payment &&
                in_array(strtolower($order->payment->payment_status), ['pending', 'created', 'initiated'])
            ) {


                // Use cache to prevent checking same order multiple times in short period
                $cacheKey = 'payment_check:' . $order->order_id;

                // Skip if we checked this order in the last 5 minutes
                // if (Cache::has($cacheKey)) {
                //     Log::info('Payment status check skipped (cached)', [
                //         'order_id' => $order->order_id
                //     ]);
                //     continue;
                // }

                try {
                    // Dispatch job to check payment status asynchronously
                    CheckRazorpayPaymentStatusJob::dispatch(
                        $order->order_id,
                        $order->payment->payment_id
                    );

                    // Cache this check for 5 minutes to prevent redundant API calls
                    Cache::put($cacheKey, true, now()->addMinutes(5));

                    Log::info('Payment status check job dispatched', [
                        'order_id' => $order->order_id,
                        'payment_id' => $order->payment->payment_id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch payment check job', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return view('frontend.order.index', [
            'orders' => $orders
        ]);
    }


    public function trackOrder(Request $request, ShiprocketService $shiprocketService)
    {
        $orderId    = $request->input('o');
        $shipmentId = $request->input('s');

        $order = Orders::where('cart_id', $orderId)->first();
        if (!$order) {
            return view('frontend.order.tracking', [
                'order' => [],
                'awb' => [],
                'response' => [],
                'tracking' => [],
                'scanDetails' => [],
                'lastScan' => [],
                'statusCode' => 404,
                'displayAwb' => [],
                'awbList' => [],
                'errorType' => 'not_found',
                'errorMessage' => 'Order not found',
            ]);
        }

        $shipment = Shipment::where('awb', $shipmentId)
            ->where('order_id', $order->order_id)
            ->first();

        if (!$shipment) {
            return view('frontend.order.tracking', [
                'order' => $order,
                'awb' => [],
                'response' => [],
                'tracking' => [],
                'scanDetails' => [],
                'lastScan' => [],
                'statusCode' => 404,
                'displayAwb' => [],
                'awbList' => [],
                'errorType' => 'not_found',
                'errorMessage' => 'Shipment not found',
            ]);
        }

        $awb = $shipment->awb;

        try {
            // ✅ Service call
            $response = $shiprocketService->trackByAwb($awb);

            Log::info('Shiprocket tracking response', [
                'order_id' => $order->order_id,
                'awb' => $awb,
                'response' => $response,
            ]);

            // ---- Handle Error Responses (Cancelled, Not Found, etc.) ----
            
            // Check if it's an error response (has 'message' but no 'tracking_data')
            if (isset($response['message']) && !isset($response['tracking_data'])) {
                $errorMessage = $response['message'];
                $errorType = $this->detectErrorType($errorMessage);
                
                // Safely get status code
                $statusCode = 500; // Default
                if (isset($response['status_code'])) {
                    $statusCode = $response['status_code'];
                }
                
                Log::warning('Shiprocket tracking error', [
                    'order_id' => $order->order_id,
                    'awb' => $awb,
                    'error_type' => $errorType,
                    'message' => $errorMessage,
                    'status_code' => $statusCode,
                ]);

                return view('frontend.order.tracking', [
                    'order' => $order,
                    'awb' => $awb,
                    'response' => $response,
                    'tracking' => [],
                    'scanDetails' => [],
                    'lastScan' => [],
                    'statusCode' => $statusCode,
                    'displayAwb' => $awb,
                    'awbList' => [$awb],
                    'errorType' => $errorType,
                    'errorMessage' => $errorMessage,
                ]);
            }

            // ---- Normalize Shiprocket response ----

            if (
                empty($response['tracking_data']) ||
                empty($response['tracking_data']['shipment_track'])
            ) {
                return view('frontend.order.tracking', [
                    'order' => $order,
                    'awb' => $awb,
                    'response' => $response,
                    'tracking' => [],
                    'scanDetails' => [],
                    'lastScan' => [],
                    'statusCode' => 200,
                    'displayAwb' => $awb,
                    'awbList' => [$awb],
                    'errorType' => 'no_data',
                    'errorMessage' => 'Tracking data not available yet.',
                ]);
            }

            $trackingData  = $response['tracking_data'];
            $tracking      = $trackingData['shipment_track'][0] ?? [];
            $scanDetails   = $trackingData['shipment_track_activities'] ?? [];

            usort($scanDetails, function ($a, $b) {
                return strtotime($b['date'] ?? '') <=> strtotime($a['date'] ?? '');
            });

            $lastScan = $scanDetails[0] ?? null;

            return view('frontend.order.tracking', [
                'order'       => $order,
                'awb'         => $awb,
                'response'    => $response,
                'tracking'    => $tracking,
                'scanDetails' => $scanDetails,
                'lastScan'    => $lastScan,
                'statusCode'  => 200,
                'displayAwb'  => $awb,
                'awbList'     => [$awb],
                'errorType'   => null,
                'errorMessage' => null,
            ]);

        } catch (\Throwable $e) {
            Log::error('Shiprocket tracking failed', [
                'order_id' => $order->order_id,
                'awb' => $awb,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('frontend.order.tracking', [
                'order' => $order,
                'awb' => $awb,
                'response' => [],
                'tracking' => [],
                'scanDetails' => [],
                'lastScan' => [],
                'statusCode' => 500,
                'displayAwb' => $awb,
                'awbList' => [$awb],
                'errorType' => 'api_error',
                'errorMessage' => 'Unable to fetch tracking details. Please try again later.',
            ]);
        }
    }

    /**
     * Detect error type from Shiprocket message
     */
    private function detectErrorType(string $message): string
    {
        $message = strtolower($message);
        
        if (str_contains($message, 'cancel')) {
            return 'cancelled';
        } elseif (str_contains($message, 'not found') || str_contains($message, 'invalid')) {
            return 'not_found';
        } elseif (str_contains($message, 'rto') || str_contains($message, 'return')) {
            return 'returned';
        }
        
        return 'general_error';
    }


    /**
     * Manually trigger payment status check for a specific order
     * Useful for "Check Payment Status" button
     */
    public function checkPaymentStatus($orderId)
    {
        $order = Orders::with('payment')
            ->where('order_id', $orderId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        if (!$order->payment) {
            return response()->json([
                'success' => false,
                'message' => 'No payment information found for this order'
            ], 404);
        }

        // Check if already processed
        if (in_array(strtolower($order->payment_status), ['paid', 'success', 'failed'])) {
            return response()->json([
                'success' => true,
                'message' => 'Order payment status is already final',
                'status' => $order->payment_status
            ]);
        }

        try {
            // Dispatch job with higher priority
            CheckRazorpayPaymentStatusJob::dispatch(
                $order->order_id,
                $order->payment->payment_id
            )->onQueue('high-priority');

            return response()->json([
                'success' => true,
                'message' => 'Payment status check initiated. Please refresh in a moment.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to trigger manual payment check', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment check'
            ], 500);
        }
    }

    public function order_success($orderId)
    {
        try {
            $decryptedId = Crypt::decrypt($orderId);
            $order = Orders::findOrFail($decryptedId);

            $this->rememberGuestOrderAccess($order);

            return view('frontend.order.success', compact('order'));
            // return redirect()->route('customer.orders.index');
        } catch (\Exception $e) {
            abort(404); // or show error page
        }
    }

    public function checkout()
    {
        $cart_items = Cart::with(['product', 'variation', 'variation.variation_attributes.attribute.attribute'])
            ->where('user_id', Auth::id())
            ->get();
        $addresses = Address::where('user_id', Auth::id())->where('is_deleted', 0)->get();
        return view('frontend.order.checkout', [
            'cart_items' => $cart_items,
            'addresses' => $addresses
        ]);
    }
    public function storeOrder(OrderStoreRequest $request)
    {
        try {
            $address = Address::where('uuid', $request->address_id)->firstOrFail();
            DB::beginTransaction();
            // Fetch cart items with products and variants
            $cart_items = Cart::with(['product', 'variation'])
                ->where('user_id', Auth::id())
                ->get();

            if ($cart_items->isEmpty()) {
                return Redirect::back()->with('error', 'Your Cart Is Empty');
            }

            // ✅ Check stock availability before proceeding
            // foreach ($cart_items as $item) {
            //     $variation = Variation::where('id', $tem->variation_id)->first();
            //     if ($variation->stock < $item->quantity) {
            //         return Redirect::back()->with('error', "Not enough stock for {$item->product->product_name}. Available: {$variation->stock}");
            //     }
            // }

            // Calculate totals
            $total_price = $cart_items->sum(function ($item) {
                return ($item->variation->price ?? $item->product->base_price) * $item->quantity;
            });
            $total_products_mrp = $cart_items->sum(function ($item) {
                return ($item->variation->mrp ?? $item->product->base_mrp) * $item->quantity;
            });
            $price_without_delivery = $total_price;

            // Generate cart ID
            $cart_id = strtoupper(substr(md5(microtime()), 0, 6));

            // Create order
            $order = Orders::create([
                'user_id' => Auth::id(),
                'store_id' => 1,
                'address_id' => $address->address_id,
                'cart_id' => $cart_id,
                'total_price' => $total_price,
                'price_without_delivery' => $price_without_delivery,
                'total_products_mrp' => $total_products_mrp,
                'payment_method' => 'COD',
                'payment_status' => 'Pending',
                'rem_price' => $price_without_delivery,
                'order_date' => Carbon::now(),
                'order_time' => now()->format('H:i:s'),
                'delivery_date' => Carbon::now()->addDays(2), // Assume delivery in 2 days
                'delivery_charge' => 0,
                'time_slot' => Carbon::now()->addDays(2),
                'order_status' => 'Pending',
                'coupon_id' => 0,
                'coupon_discount' => 0,
                'note' => NULL,
            ]);

            if ($order) {
                foreach ($cart_items as $item) {
                    StoreOrders::create([
                        'product_name' => $item->product->product_name,
                        'varient_image' => $item->product->product_image,
                        'quantity' => $item->quantity,
                        'qty' => $item->quantity,
                        'varient_id' => $item->variation_id,
                        'price' => $item->variation->price ?? $item->product->base_price * $item->quantity,
                        'total_mrp' => $item->variation->mrp ?? $item->product->base_mrp * $item->quantity,
                        'order_cart_id' => $cart_id,
                        'order_date' => Carbon::now(),
                        'store_id' => 1,
                        'description' => $item->product->description,
                    ]);

                    $item->variation->where('id', $item->variation_id)->decrement('stock', $item->quantity);
                }
                // SendOrderPlaceMailEvent::dispatch($order->load(['orderItems', 'address']));
            }

            // Clear cart after successful order placement
            Cart::where('user_id', Auth::id())->delete();

            DB::commit();
            return Redirect::route('successOrder', $cart_id)->with('success', 'Order Created Successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()->with('error', $e->getMessage());
        }
    }
    public function successOrder(Request $request, $cart_id)
    {
        $order = Orders::with(['orderItems.variation.variation_attributes.attribute.attribute', 'address'])
            ->where('user_id', Auth::id())
            ->where('cart_id', $cart_id)
            ->firstOrFail();
        $pdf = Pdf::loadView('frontend.order.invoice', ['order' => $order]);
        $pdf->render();
        $pdfContent = $pdf->output();
        $directory = 'orders';
        $filename = 'order-generate-' . $order->cart_id . '.pdf';
        $path = $directory . '/' . $filename;
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        Storage::disk('public')->put($path, $pdfContent);
        SendOrderPlacedMailEvent::dispatch($order);
        return view('frontend.order.success_order', ['order' => $order]);
    }

    public function addressStore(AddressStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $city = City::create([
                'city_name' => $request->city
            ]);
            $society = Society::create([
                'society_name' => $request->society,
                'city_id' => $city->id
            ]);
            Address::create([
                'type' => $request->type,
                'user_id' => Auth::id(),
                'receiver_name' => $request->receiver_name,
                'receiver_phone' => $request->receiver_phone,
                'city' => $request->city,
                'society' => $request->society,
                'city_id' => $city->id,
                'society_id' => $society->id,
                'house_no' => $request->house_no,
                'landmark' => $request->type,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'lat' => 0,
                'lng' => 0,
                'select_status' => 1,
                'added_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'receiver_email' => $request->receiver_email
            ]);
            DB::commit();
            return Redirect::route('checkout.index')->with('success', 'Address Created Successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()->with('error', $e->getMessage());
        }
    }

    public function updateAddress(AddressUpdateRequest $request)
    {
        try {
            $address = Address::where('uuid', $request->address_id)->first();
            if (!$address) {
                return Redirect::back()->with('error', 'Address not found');
            }
            $society = Society::where('society_id', $address->society_id)->first();
            $city = City::where('city_id', $address->city_id)->first();
            $society->update([
                'society_name' => $request->society
            ]);
            $city->update([
                'city_name' => $request->city
            ]);
            $data = [
                'receiver_name' => $request->receiver_name,
                'receiver_phone' => $request->receiver_phone,
                'house_no' => $request->house_no,
                'society' => $request->society,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'type' => $request->type,
                'receiver_email' => $request->receiver_email
            ];
            $address->update($data);
            return Redirect::route('checkout.index')->with('success', 'Address Updated Successfully');
        } catch (Exception $e) {
            return Redirect::back()->with('error', $e->getMessage());
        }
    }
    public function deleteAddress(Request $request, $address_id)
    {
        $query = Address::where('uuid', $address_id);

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $guestAddressUuids = array_values(array_filter((array) session('guest_checkout_address_uuids', [])));

            abort_if(!in_array($address_id, $guestAddressUuids, true), 403);
            $query->whereIn('uuid', $guestAddressUuids);
        }

        $address = $query->firstOrFail();
        $address->update([
            'is_deleted' => true
        ]);

        if (!Auth::check()) {
            session()->put(
                'guest_checkout_address_uuids',
                array_values(array_diff((array) session('guest_checkout_address_uuids', []), [$address_id]))
            );
        }

        return Redirect::route('checkout.index')->with('success', 'Address Deleted Successfully');
    }


    /**
     * Customer cancels their own order (only before confirmation).
     * Mirrors AdminorderController::ajaxCancelOrder flow but scoped to authenticated user.
     */
    public function cancelOrder(Request $request, $cart_id)
    {
        try {
            $guestAccess = $this->getGuestOrderAccess();
            $isGuest = !Auth::check() && !empty($guestAccess);
            $userId = Auth::id() ?? ($guestAccess['user_id'] ?? null);

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in (or have a guest session) to cancel an order.'
                ], 401);
            }

            $orderQuery = Orders::with(['address', 'user'])
                ->where('cart_id', $cart_id)
                ->where('user_id', $userId);

            if ($isGuest) {
                $orderQuery->where('is_guest_checkout', true);
            }

            $order = $orderQuery->firstOrFail();

            if (strcasecmp($order->order_status, 'Pending') !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending (unconfirmed) orders can be cancelled.'
                ], 422);
            }

            $reason = trim((string) $request->input('cancel_reason', '')) ?: 'Cancelled by customer';

            if (strcasecmp($order->payment_method, 'COD') === 0) {
                $this->customerHandleCODCancellation($order, $reason);
            } else {
                $payment = OrderPayments::where('order_id', $order->order_id)->first();

                if (!$payment) {
                    $order->update([
                        'order_status' => 'Cancelled',
                        'payment_status' => 'Not Applicable',
                    ]);
                    CancelledOrder::updateOrCreate(
                        ['order_id' => $order->order_id],
                        [
                            'user_id' => $userId,
                            'comment' => $reason,
                            'amount' => $order->total_price,
                            'is_refunded' => 'no',
                            'cancellation_status' => 'no_payment',
                            'read_at' => now(),
                        ]
                    );
                    $this->sendCustomerCancellationNotifications($order);

                    return response()->json([
                        'success' => true,
                        'message' => 'Order cancelled (no payment record found).'
                    ]);
                }

                $result = $this->customerHandleOnlinePaymentCancellation($order, $payment, $reason, $userId);

                if (!$result['ok']) {
                    // Refund did not succeed — order remains uncancelled. Tell user to retry.
                    return response()->json([
                        'success' => false,
                        'message' => $result['message'] ?? 'Refund could not be processed. Please try again.',
                    ], $result['status'] ?? 502);
                }

                $this->sendCustomerCancellationNotifications($order);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'] ?? 'Order cancelled successfully.',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Customer Cancel Order Error', [
                'error' => $e->getMessage(),
                'cart_id' => $cart_id,
                'user_id' => Auth::id() ?? (session('guest_order_access.user_id') ?? null),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }

    private function customerHandleCODCancellation(Orders $order, string $reason): void
    {
        $shiprocket = new ShiprocketService();

        if ($order->shipment) {
            $shiprocketStatus = strtoupper($order->shipment->status ?? '');

            try {
                if ($shiprocketStatus === 'NEW' && empty($order->shipment->awb)) {
                    $shiprocket->cancelOrder($order->shipment->shipment_order_id);
                } elseif (in_array($shiprocketStatus, ['READY TO SHIP', 'PICKUP GENERATED', 'PICKUP SCHEDULED', 'MANIFESTED', 'LABEL GENERATED']) && $order->shipment->awb) {
                    $shiprocket->cancelShipment($order->shipment->awb);
                }
            } catch (\Exception $e) {
                Log::error('Customer cancel: Shiprocket cancel failed', [
                    'order_id' => $order->order_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $order->update([
            'order_status' => 'Cancelled',
            'payment_status' => 'Not Applicable',
        ]);

        CancelledOrder::updateOrCreate(
            ['order_id' => $order->order_id],
            [
                'user_id' => $order->user_id,
                'comment' => $reason,
                'amount' => $order->total_price,
                'is_refunded' => 'no',
                'cancellation_status' => 'cod_cancelled',
                'read_at' => now(),
            ]
        );
    }

    /**
     * Cancels an online-paid order ONLY if refund succeeds (or no refund applicable).
     * Order state is NOT changed if a refund attempt was made but failed — caller must
     * surface the error so the customer can retry. Returns ['ok'=>bool, 'message'=>string, 'status'=>int].
     */
    private function customerHandleOnlinePaymentCancellation(Orders $order, OrderPayments $payment, string $reason, int $userId): array
    {
        $paymentStatus = strtolower((string) ($payment->payment_status ?? ''));
        $razorpayPaymentId = (string) ($payment->payment_id ?? '');
        $refundEligible = strpos($razorpayPaymentId, 'pay_') === 0
            && in_array($paymentStatus, ['paid', 'success', 'captured']);

        $refundOutcome = null;

        if ($refundEligible) {
            $refundOutcome = $this->customerProcessRefund($order, $payment, $userId);
            if (!$refundOutcome['ok']) {
                Log::warning('Customer cancel aborted: refund failed', [
                    'order_id' => $order->order_id,
                    'payment_id' => $razorpayPaymentId,
                    'reason' => $refundOutcome['message'],
                ]);
                return [
                    'ok' => false,
                    'message' => $refundOutcome['message'] ?: 'Refund could not be processed. Please try again.',
                    'status' => $refundOutcome['status'] ?? 502,
                ];
            }
        } else {
            Log::info('Customer cancel: refund skipped (no captured Razorpay payment)', [
                'order_id' => $order->order_id,
                'payment_id' => $razorpayPaymentId,
                'payment_status' => $paymentStatus,
            ]);
        }

        // Refund either succeeded or was not applicable — proceed with cancellation.
        $cancellationStatus = 'no_shipment';

        if ($order->shipment) {
            $shiprocketStatus = strtoupper(trim($order->shipment->status ?? ''));
            $shiprocket = new ShiprocketService();

            try {
                if ($shiprocketStatus === 'NEW' && empty($order->shipment->awb)) {
                    $shiprocket->cancelOrder($order->shipment->shipment_order_id);
                    $cancellationStatus = 'cancelled_not_shipped';
                } elseif (in_array($shiprocketStatus, ['READY TO SHIP', 'PICKUP GENERATED', 'PICKUP SCHEDULED', 'MANIFESTED', 'LABEL GENERATED']) && $order->shipment->awb) {
                    $shiprocket->cancelShipment($order->shipment->awb);
                    $cancellationStatus = 'cancelled_before_pickup';
                } else {
                    $cancellationStatus = 'cancelled';
                }
            } catch (\Exception $e) {
                Log::error('Customer cancel: Shiprocket cancel failed (online)', [
                    'order_id' => $order->order_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($refundEligible) {
            $newPaymentStatus = 'Refunded';
            $isRefunded = 'yes';
        } else {
            $newPaymentStatus = 'Not Applicable';
            $isRefunded = 'no';
            if ($cancellationStatus === 'no_shipment') {
                $cancellationStatus = 'no_paid_capture';
            }
        }

        $order->update([
            'order_status' => 'Cancelled',
            'payment_status' => $newPaymentStatus,
        ]);

        if ($order->shipment) {
            $order->shipment->update(['status' => $cancellationStatus]);
        }

        CancelledOrder::updateOrCreate(
            ['order_id' => $order->order_id],
            [
                'user_id' => $userId,
                'comment' => $reason,
                'amount' => $order->total_price,
                'is_refunded' => $isRefunded,
                'cancellation_status' => $cancellationStatus,
                'refunded_at' => $refundEligible ? now() : null,
                'read_at' => now(),
            ]
        );

        return [
            'ok' => true,
            'message' => $refundEligible
                ? 'Order cancelled and refund processed successfully.'
                : 'Order cancelled successfully.',
            'status' => 200,
        ];
    }

    /**
     * Razorpay refund call. Never throws — returns structured result.
     * Does NOT mutate order state; caller decides what to do based on outcome.
     */
    private function customerProcessRefund(Orders $order, OrderPayments $payment, int $userId): array
    {
        $refundAmount = $order->total_price;
        $razorpayPaymentId = $payment->payment_id;

        try {
            $api = new Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $refundResponse = $api->payment->fetch($razorpayPaymentId)->refund([
                'amount' => $refundAmount * 100,
                'speed' => 'normal',
            ]);

            if (empty($refundResponse->id)) {
                Log::error('Customer cancel: Razorpay returned empty refund id', [
                    'order_id' => $order->order_id,
                    'payment_id' => $razorpayPaymentId,
                ]);
                return [
                    'ok' => false,
                    'message' => 'Refund could not be confirmed. Please try again.',
                    'status' => 502,
                ];
            }

            OrderRefund::create([
                'order_id' => $order->order_id,
                'payment_id' => $razorpayPaymentId,
                'refund_id' => $refundResponse->id,
                'status' => $refundResponse->status,
                'amount' => $refundAmount,
                'method' => 'razorpay',
                'refunded_at' => now(),
                'created_by' => $userId,
            ]);

            return [
                'ok' => true,
                'refund_id' => $refundResponse->id,
                'status_code' => 200,
            ];
        } catch (\Razorpay\Api\Errors\BadRequestError $e) {
            $msg = $e->getMessage();
            Log::error('Customer cancel: Razorpay refund BadRequestError', [
                'order_id' => $order->order_id,
                'payment_id' => $razorpayPaymentId,
                'error' => $msg,
            ]);

            $userMsg = (strpos($msg, 'does not have enough balance') !== false)
                ? 'We are unable to process your refund right now. Please try again later.'
                : 'Refund failed. Please try again. (' . $msg . ')';

            return [
                'ok' => false,
                'message' => $userMsg,
                'status' => 502,
            ];
        } catch (\Exception $e) {
            Log::error('Customer cancel: Refund failed (generic)', [
                'order_id' => $order->order_id,
                'payment_id' => $razorpayPaymentId,
                'error' => $e->getMessage(),
            ]);
            return [
                'ok' => false,
                'message' => 'Refund failed. Please try again.',
                'status' => 500,
            ];
        }
    }

    private function sendCustomerCancellationNotifications(Orders $order): void
    {
        try {
            $recipients = array_filter(array_unique([
                $order->address->receiver_email ?? null,
                $order->user->email ?? null,
            ]));

            if (!empty($recipients)) {
                Mail::to($recipients)->send(new CancelOrder($order));
            }

            $adminEmail = config('mail.admin_finance_email') ?: config('mail.from.address');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new CancelOrder($order));
            }
        } catch (\Exception $e) {
            Log::error('Customer cancel: Notification email failed', [
                'order_id' => $order->order_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function tracking(Request $request, Orders $order)
    {
        // Ensure the authenticated user owns the order
        $user = $request->user();
        if (!$user || $order->user_id !== $user->id) {
            abort(403, 'You are not authorized to view this order.');
        }

        // allow overriding AWB via query param (useful if order has multiple shipments)
        $awb = $order->shipment->awb ?? $request->query('awb');
        if (!$awb) {
            return back()->with('error', 'No AWB / tracking number found for this order.');
        }

        $trackingApiUrl = config('services.ithinklogistics');
        $accessToken = config('services.ithinklogistics.access_token');
        $secret_key = config('services.ithinklogistics.secret_key');

        $response = null;
        $responseData = null;
        $statusCode = null;

        try {
            if ($trackingApiUrl) {
                $client = new Client();

                // Normalize outgoing awb_number_list (support CSV -> array)
                $payloadAwb = $awb;
                if (is_string($awb) && strpos($awb, ',') !== false) {
                    $payloadAwb = array_map('trim', explode(',', $awb));
                }

                $resp = $client->post("https://api.ithinklogistics.com/api_v3/order/track.json", [
                    'json' => [
                        'data' => [
                            'awb_number_list' => $payloadAwb,
                            'access_token' => $accessToken,
                            'secret_key' => $secret_key,
                        ]
                    ],
                    'timeout' => 30,
                ]);
                $body = (string) $resp->getBody();
                $response = json_decode($body, true) ?? [];
                // dd( $response, $order);
                $statusCode = $response['status_code'] ?? $resp->getStatusCode();
            } else {
                // fallback for local dev
                $response = [
                    // ... keep or remove sample data as needed ...
                ];
                $statusCode = 200;
            }

            // Prepare list of requested AWBs (array)
            $requestedAwbs = is_string($awb) && strpos($awb, ',') !== false
                ? array_map('trim', explode(',', $awb))
                : [$awb];

            $data = $response['data'] ?? [];

            // Try direct key match (data keyed by AWB)
            foreach ($requestedAwbs as $rAwb) {
                if (isset($data[$rAwb])) {
                    $responseData = $data[$rAwb];
                    break;
                }
            }

            // Try matching inside entries (awb_no or awb_number_list), else fallback to single entry
            if (!$responseData && !empty($data)) {
                foreach ($data as $key => $entry) {
                    if (!is_array($entry)) {
                        continue;
                    }

                    // match awb_no
                    if (!empty($entry['awb_no'])) {
                        foreach ($requestedAwbs as $rAwb) {
                            if ((string)$entry['awb_no'] === (string)$rAwb) {
                                $responseData = $entry;
                                break 2;
                            }
                        }
                    }

                    // match awb_number_list inside entry
                    if (!empty($entry['awb_number_list'])) {
                        $anl = $entry['awb_number_list'];
                        $list = [];
                        if (is_array($anl)) {
                            $list = $anl;
                        } elseif (is_string($anl)) {
                            if (strpos($anl, ',') !== false) {
                                $list = array_map('trim', explode(',', $anl));
                            } else {
                                $list = [(string)$anl];
                            }
                        }
                        foreach ($requestedAwbs as $rAwb) {
                            if (in_array((string)$rAwb, $list, true)) {
                                $responseData = $entry;
                                break 2;
                            }
                        }
                    }

                    // also check if the numeric key equals requested awb
                    foreach ($requestedAwbs as $rAwb) {
                        if ((string)$key === (string)$rAwb) {
                            $responseData = $entry;
                            break 2;
                        }
                    }
                }

                if (!$responseData && count($data) === 1) {
                    $responseData = reset($data);
                }
            }
        } catch (\Throwable $e) {
            Log::error('User tracking API error', [
                'user_id' => $user->id ?? null,
                'order_id' => $order->order_id,
                'awb' => $awb,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to fetch tracking details: ' . $e->getMessage());
        }

        if (!$responseData) {
            $trackingUrl = json_decode($order->shipment->response, true)['data'][1]['tracking_url'] ?? null;
            if ($trackingUrl) {
                return redirect()->away($trackingUrl);
            }
            return back()->with('error', 'Tracking data not available for AWB: ' . $awb);
        }

        // Normalize awbList for view
        $awbList = [];
        if (!empty($responseData['awb_number_list'])) {
            $anl = $responseData['awb_number_list'];
            if (is_array($anl)) {
                $awbList = $anl;
            } elseif (is_string($anl) && strpos($anl, ',') !== false) {
                $awbList = array_map('trim', explode(',', $anl));
            } else {
                $awbList = [(string)$anl];
            }
        } elseif (!empty($responseData['awb_no'])) {
            $awbList = [(string)$responseData['awb_no']];
        } else {
            $awbList = $requestedAwbs;
        }

        // $displayAwb = $responseData['awb_no'] ?? ($awbList[0] ?? $awb);

        $displayAwb = $tracking['awb'] ?? $awb;

        if (is_array($displayAwb)) {
            $displayAwb = $displayAwb[0] ?? '';
        }

        // Sort scan details descending
        $scanDetails = $responseData['scan_details'] ?? [];
        usort($scanDetails, function ($a, $b) {
            $at = strtotime($a['scan_date_time'] ?? ($a['status_date_time'] ?? ''));
            $bt = strtotime($b['scan_date_time'] ?? ($b['status_date_time'] ?? ''));
            return $bt <=> $at;
        });
        $lastScan = $responseData['last_scan_details'] ?? ($scanDetails[0] ?? null);

        return view('frontend.order.tracking', [
            'order' => $order,
            'awb' => $awb,
            'response' => $response,
            'tracking' => $responseData,
            'scanDetails' => $scanDetails,
            'lastScan' => $lastScan,
            'statusCode' => $statusCode,
            'displayAwb' => $displayAwb,
            'awbList' => $awbList,
        ]);
    }
}
