<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\CompleteOrderMailEvent;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\CancelledOrder;
use App\Models\OrderRefund;
use App\Models\OrderPayments;
use App\Models\User;
use App\Models\UserPointHistory;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Services\ShiprocketService;
use App\Mail\OrderConfirmedMail;
use App\Mail\OrderShippedMail;
use App\Mail\CompleteOrder;
use App\Mail\OrderCancelledMail;
use App\Mail\CancelOrder;
use App\Mail\OrderProcessingMail;
use App\Mail\OrderOutForDeliveredMail;
use App\Models\Orders;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
// use App\Traits\SmsTrait;
use Carbon\Carbon;
use Exception;
use Razorpay\Api\Api;

class ShiprocketWebhookController extends Controller
{
    // use SmsTrait;

    /**
     * Handle incoming webhook from Shiprocket
     */
    public function handleShiprocketWebhook(Request $request)
    {
        try {
            // Optional secret validation
            if ($request->header('x-api-key') !== config('services.shiprocket.webhook_secret')) {
                Log::warning("Invalid Shiprocket x-api-key received", [
                    'received' => $request->header('x-api-key')
                ]);
                // Do NOT return 401 here
            }

            // Log the incoming webhook
            Log::info('Shiprocket webhook received', [
                'payload' => $request->all()
            ]);

            $payload = $request->all();

            // Extract necessary data from Shiprocket webhook payload
            $shiprocketOrderId = $payload['order_id'] ?? $payload['id'] ?? null;
            $shiprocketStatus = $payload['current_status'] ?? $payload['status'] ?? null;
            $awbCode = $payload['awb'] ?? $payload['awb_code'] ?? null;
            $shipmentId = $payload['shipment_id'] ?? null;

            if (!$shiprocketOrderId) {
                Log::warning('Shiprocket webhook: No order ID found in payload');
                return response()->json(['status' => 'Error', 'message' => 'No order ID'], 400);
            }

            // Find order by shipment_order_id or awb_code
            $shipment = Shipment::where('shipment_order_id', $shiprocketOrderId)
                ->orWhere('awb', $awbCode)
                ->with([
                    'order',
                ])
                ->first();

            if (!$shipment) {
                Log::warning('Shiprocket webhook: Order not found', [
                    'shipment_order_id' => $shiprocketOrderId,
                    'awb_code' => $awbCode
                ]);
                return response()->json(['status' => 'OK', 'message' => 'Order not found'], 200);
            }

            // Store tracking information
            $this->storeTrackingData($shipment, $payload);

            // **CHECK FOR RTO DELIVERY STATUS FIRST** (for cancelled orders awaiting return)
            if ($this->isRTODelivered($shiprocketStatus) && $shipment->order->order_status === 'Cancelled') {
                Log::info('RTO Delivered - Processing refund for cancelled order', [
                    'order_id' => $shipment->order->order_id,
                    'shiprocket_status' => $shiprocketStatus,
                    'awb_code' => $awbCode
                ]);

                // Update shipment status if exists
                if ($shipment->order->shipment) {
                    $shipment->order->shipment->update([
                        'status' => 'RTO_DELIVERED',
                        'rto_delivered_at' => now()
                    ]);
                }

                // Process refund for returned product
                $this->processReturnedProductRefund($shipment->order);

                return response()->json([
                    'status' => 'OK',
                    'message' => 'RTO delivered and refund processed'
                ], 200);
            }

            // Map Shiprocket status to our internal status
            $newStatus = $this->mapShiprocketStatus($shiprocketStatus);

            if (!$newStatus) {
                Log::info('Shiprocket webhook: Unhandled status', ['status' => $shiprocketStatus]);
                return response()->json(['status' => 'OK'], 200);
            }

            // Update order status for normal flow
            return $this->updateOrderStatus($shipment, $newStatus, $awbCode, $shiprocketStatus, $shipmentId);
        } catch (\Exception $e) {
            Log::error('Shiprocket webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'Error',
                'message' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * Store tracking data from webhook
     */
    private function storeTrackingData($shipment, $payload)
    {
        try {
            // Extract tracking information from payload
            $trackingData = [
                'order_id' => $shipment->order->order_id,
                'awb_number' => $payload['awb'] ?? $payload['awb_code'] ?? $shipment->awb,
                'logistics_name' => $payload['courier_name'] ?? $payload['courier'] ?? $shipment->courier_name ?? null,
                'current_tracking_status' => $payload['current_status'] ?? $payload['status'] ?? null,
                'status' => $this->mapShiprocketStatus($payload['current_status'] ?? $payload['status'] ?? ''),
                'remark' => $payload['remark'] ?? $payload['remarks'] ?? $payload['comment'] ?? null,
                'location' => $payload['location'] ?? $payload['scan_location'] ?? null,
                'latest_scan_time' => isset($payload['scan_datetime']) ?
                    Carbon::parse($payload['scan_datetime']) : (isset($payload['updated_at']) ? Carbon::parse($payload['updated_at']) : null),
                'edd_date' => isset($payload['edd']) ? Carbon::parse($payload['edd']) : null,
                'tracking_url' => $payload['tracking_url'] ?? $payload['track_url'] ?? null,
                'raw_payload' => json_encode($payload),
            ];

            // Create or update tracking record
            ShipmentTracking::updateOrCreate(
                [
                    'order_id' => $shipment->order->order_id,
                    'awb_number' => $trackingData['awb_number'],
                ],
                $trackingData
            );
            // dd( $trackingData);
            Log::info('Tracking data stored successfully', [
                'order_id' => $shipment->order->order_id,
                'awb' => $trackingData['awb_number'],
                'status' => $trackingData['current_tracking_status']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store tracking data: ' . $e->getMessage(), [
                'order_id' => $shipment->order->order_id,
                'payload' => $payload
            ]);
            // Don't throw exception, just log it - tracking data is supplementary
        }
    }

    /**
     * Check if the Shiprocket status indicates RTO delivery
     */
    private function isRTODelivered($status)
    {
        $rtoDeliveredStatuses = [
            'RTO DELIVERED',
            'RTO-DELIVERED',
            'RTO_DELIVERED',
            'RETURNED',
            'RTO RECEIVED',
            'RTO-RECEIVED',
            'RTO_RECEIVED'
        ];

        return in_array(strtoupper($status), $rtoDeliveredStatuses);
    }

    /**
     * Process refund after product is returned
     */
    private function processReturnedProductRefund($order)
    {
        $payment = OrderPayments::where('order_id', $order->order_id)->first();

        if (!$payment) {
            Log::error('Payment record not found for returned order', [
                'order_id' => $order->order_id
            ]);
            throw new \Exception('Payment record not found for returned order');
        }

        $user = User::find($order->user_id);

        // Update order payment status
        $order->update([
            'payment_status' => 'Refund Initiated'
        ]);

        // Update cancelled order record
        CancelledOrder::where('order_id', $order->order_id)
            ->update([
                'is_refunded' => 'processing',
                'cancellation_status' => 'product_returned',
                'returned_at' => now()
            ]);

        // Process the refund
        $this->processRefund($order, $payment, $user);

        Log::info('Refund processed after product return', [
            'order_id' => $order->order_id,
            'amount' => $order->total_price
        ]);

        // Optional: Send notification to customer
        // $this->notifyCustomerRefundProcessed($order, $user);
    }

    /**
     * Handle order cancellation from admin or Shiprocket
     */
    public function cancelOrder(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required',
                'cancel_reason' => 'nullable|string',
                'cancelled_by' => 'required|string|in:admin,shiprocket'
            ]);

            DB::beginTransaction();

            $order = Orders::with([
                'user',
                'cancel_detail',
                'coupon',
                'address',
                'payment',
                'shipment',
                'items.product',
                'items.variation',
                'items.variation.variation_attributes.attribute_options.attribute'
            ])->find($request->order_id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Check if order can be cancelled
            if (in_array($order->shipment->status, ['shipped', 'out for delivery', 'delivered'])) {
                throw new Exception("Cannot cancel an order that has been shipped or delivered.");
            }

            $oldStatus = $order->shipment->status;
            $order->shipment->status = 'cancelled';
            $order->save();

            // Cancel in Shiprocket if cancelled by admin
            if ($request->cancelled_by === 'admin' && $order->shiprocket_shipment_id) {
                $shiprocket = new ShiprocketService();
                $shiprocketStatus = strtoupper($order->shipment->status ?? '');

                // Determine cancellation method based on Shiprocket status
                if ($shiprocketStatus == 'NEW' && empty($order->shipment->awb)) {
                    // Status: NEW - Order just created, no AWB assigned
                    // Method: cancelOrder API
                    try {
                        $shiprocket->cancelOrder($order->shipment->shipment_order_id);
                        Log::info('Order cancelled in Shiprocket (NEW status)', [
                            'order_id' => $order->order_id,
                            'shipment_order_id' => $order->shipment->shipment_order_id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to cancel order in Shiprocket', [
                            'order_id' => $order->order_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                } elseif (in_array($shiprocketStatus, ['READY TO SHIP', 'PICKUP GENERATED', 'PICKUP SCHEDULED', 'MANIFESTED', 'LABEL GENERATED'])) {
                    // Status: AWB assigned but courier hasn't picked up yet
                    // Method: cancelShipment API using AWB code
                    if ($order->shipment->awb) {
                        try {
                            $shiprocket->cancelShipment($order->shipment->awb);
                            Log::info('Shipment cancelled in Shiprocket via AWB', [
                                'order_id' => $order->order_id,
                                'shiprocket_status' => $shiprocketStatus,
                                'awb_code' => $order->shipment->awb
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to cancel shipment in Shiprocket', [
                                'order_id' => $order->order_id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    } else {
                        Log::warning('Cannot cancel shipment - AWB code missing', [
                            'order_id' => $order->order_id,
                            'shiprocket_status' => $shiprocketStatus
                        ]);
                    }
                } elseif (in_array($shiprocketStatus, ['PICKED UP', 'IN TRANSIT', 'SHIPPED'])) {
                    // Status: Package already with courier - Cannot cancel directly
                    // Would need RTO (Return to Origin) process
                    Log::warning('Cannot cancel - shipment already picked up. Consider RTO process.', [
                        'order_id' => $order->order_id,
                        'shiprocket_status' => $shiprocketStatus,
                        'awb_code' => $order->shipment->awb
                    ]);
                    // Continue with refund processing but order won't be cancelled in Shiprocket
                } else {
                    // Unknown status or status that doesn't need Shiprocket cancellation
                    Log::info('Shiprocket cancellation skipped', [
                        'order_id' => $order->order_id,
                        'shiprocket_status' => $shiprocketStatus,
                        'reason' => 'Status does not require or allow cancellation'
                    ]);
                }
            }

            // Process refunds
            $this->processRefunds($order, $request);

            // Create order log
            // OrderLog::create([
            //     'order_id' => $order->order_id,
            //     'user_id' => auth()->id() ?? 1,
            //     'old_status' => $oldStatus,
            //     'new_status' => 'cancelled',
            //     'note' => $request->cancel_reason ?? 'Order cancelled via ' . $request->cancelled_by,
            // ]);

            // Send notifications
            $this->sendCancellationNotifications($order);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Order cancellation failed: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => "Cancellation failed: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map Shiprocket status to internal order status
     */
    private function mapShiprocketStatus($shiprocketStatus)
    {
        $statusMap = [
            // 'NEW' status is not mapped - admin confirms order and creates in Shiprocket
            'READY TO SHIP' => 'processing',
            'PICKUP SCHEDULED' => 'processing',
            'PICKUP GENERATED' => 'processing',
            'OUT FOR PICKUP' => 'processing',
            'MANIFESTED' => 'processing',
            'SHIPPED' => 'shipped',
            'IN TRANSIT' => 'shipped',
            'OUT FOR DELIVERY' => 'out for delivery',
            'DELIVERED' => 'delivered',
            'CANCELED' => 'cancelled',
            'CANCELLED' => 'cancelled',
            'RTO INITIATED' => 'cancelled',
            'RTO DELIVERED' => 'cancelled',
            'LOST' => 'cancelled',
            'DAMAGED' => 'cancelled',
        ];

        return $statusMap[strtoupper($shiprocketStatus)] ?? null;
    }

    /**
     * Update order status based on webhook or manual cancellation
     */
    private function updateOrderStatus($shipment, $newStatus, $awbCode = null, $shiprocketStatus = null, $shipmentId = null)
    {
        DB::beginTransaction();

        try {
            $oldStatus = $shipment->status;

            // Don't downgrade status
            if ($this->shouldSkipStatusUpdate($oldStatus, $newStatus)) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Status update skipped (no downgrade)'
                ]);
            }

            $shipment->status = $newStatus;

            // Update AWB code if provided
            if ($awbCode) {
                $shipment->awb = $awbCode;
            }

            // Update Shiprocket status if provided
            if ($shiprocketStatus) {
                $shipment->status = $shiprocketStatus;
            }

            // Update shipment ID if provided and not already set
            if ($shipmentId && !$shipment->shiprocket_shipment_id) {
                $shipment->shiprocket_shipment_id = $shipmentId;
            }

            $shipment->save();

            $recipient = $shipment->order->address->receiver_email ?? 'info@ammasspices.com ';
            $phone = $shipment->order->address->receiver_phone ?? $shipment->order->user->user_phone;
            $orderId = $shipment->order->cart_id;
            $user_name = $shipment->order->address->receiver_name ?? $shipment->order->user->name;

            // Handle status-specific logic
            switch ($newStatus) {
                // 'confirmed' case removed - admin confirms and creates order in Shiprocket

                case 'processing':
                    $this->handleProcessingStatus($shipment->order, $recipient, $phone, $orderId);
                    break;

                case 'shipped':
                    $this->handleShippedStatus($shipment->order, $recipient, $phone, $orderId);
                    break;

                case 'out for delivery':
                    $this->handleOutForDeliveryStatus($shipment->order, $recipient, $phone, $orderId);
                    break;

                case 'delivered':
                    $this->handleDeliveredStatus($shipment->order, $recipient, $phone, $orderId);
                    break;

                case 'cancelled':
                    $this->handleCancelledStatus($shipment->order, $recipient, $phone, $orderId, $user_name);
                    break;
            }

            // Create order log
            OrderLog::create([
                'order_id' => $shipment->order->order_id,
                'user_id' => auth()->id() ?? 1,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => 'Updated via Shiprocket webhook' . ($shiprocketStatus ? " - Shiprocket Status: {$shiprocketStatus}" : ''),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Order status updated to {$newStatus} successfully."
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Order status update failed: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => "Something went wrong: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if status update should be skipped
     */
    private function shouldSkipStatusUpdate($oldStatus, $newStatus)
    {
        $statusHierarchy = [
            'pending' => 1,
            'confirmed' => 2,
            'processing' => 3,
            'shipped' => 4,
            'out for delivery' => 5,
            'delivered' => 6,
            'cancelled' => 7
        ];

        $oldLevel = $statusHierarchy[$oldStatus] ?? 0;
        $newLevel = $statusHierarchy[$newStatus] ?? 0;

        // Allow cancelled at any time, but don't downgrade otherwise
        if ($newStatus === 'cancelled') {
            return false;
        }

        return $newLevel <= $oldLevel;
    }

    /**
     * Handle confirmed status - NOT USED IN WEBHOOK
     * Admin confirms order status in OrderController, which creates order in Shiprocket
     */

    /**
     * Handle processing status
     */
    private function handleProcessingStatus($order, $recipient, $phone, $orderId)
    {
        if ($order->shipment) {
            // $order->shipment->update([
            //     'status' => 'processing',
            // ]);


            $order->shipment()->update([
                'status' => 'processing',
            ]);
        }
        // try {
        //     Mail::to($recipient)->send(new OrderProcessingMail($order, $recipient));
        // } catch (Exception $e) {
        //     Log::warning("Email - Processing failed: " . $e->getMessage());
        // }

        // try {
        //     $this->sendPushMessage($order->user_id, ['order_id' => $orderId, 'productId' => $order->order_id], 18);
        // } catch (Exception $e) {
        //     Log::warning("Push - Processing failed: " . $e->getMessage());
        // }

        // $this->sendSmsSafe($phone, "Order {$orderId} is currently being packed. We'll notify you once it ships. MISS25");
    }

    /**
     * Handle shipped status
     */
    private function handleShippedStatus($order, $recipient, $phone, $orderId)
    {
        // $order->order_status = 'shipped';
        // // $order->delivery_date = Carbon::now();
        // $order->save();

        $order->shipment()->update([
            'status' => 'shipped',
        ]);
        // try {
        //     Mail::to($recipient)->send(new OrderShippedMail($order, $recipient));
        // } catch (Exception $e) {
        //     Log::warning("Email - Shipped failed: " . $e->getMessage());
        // }

        // try {
        //     $this->sendPushMessage($order->user_id, ['order_id' => $orderId, 'productId' => $order->order_id], 19);
        // } catch (Exception $e) {
        //     Log::warning("Push - Shipped failed: " . $e->getMessage());
        // }

        $trackLink = "https://bodhiblisssoap/track-order/{$orderId}";
        // $this->sendSmsSafe($phone, "Your order {$orderId} has been shipped. Track your delivery status here: {$trackLink} MISSTF");
    }

    /**
     * Handle out for delivery status
     */
    private function handleOutForDeliveryStatus($order, $recipient, $phone, $orderId)
    {

        // $order->order_status = 'out_for_delivery';
        // // $order->delivery_date = Carbon::now();
        // $order->save();
        $order->shipment()->update([
            'status' => 'out_for_delivery',
        ]);
        // try {
        //     $this->sendPushMessage($order->user_id, ['order_id' => $orderId, 'productId' => $order->order_id], 20);
        // } catch (Exception $e) {
        //     Log::warning("Push - Out for delivery failed: " . $e->getMessage());
        // }

        // try {
        //     Mail::to($recipient)->send(new OrderOutForDeliveredMail($order, $recipient));
        // } catch (Exception $e) {
        //     Log::warning("Email - Delivered failed: " . $e->getMessage());
        // }

        // $this->sendSmsSafe($phone, "Your order {$orderId} is out for delivery today. Please ensure someone is available to receive it. MISS25");
    }

    /**
     * Handle delivered status
     */
    private function handleDeliveredStatus($order, $recipient, $phone, $orderId)
    {
        // $order->payment_status = 'success';
        $order->order_status = 'Completed';
        $order->is_view = 1;

        $order->delivery_date = Carbon::now();
        $order->save();
        $order->shipment()->update([
            'status' => 'delivered',
        ]);

        try {
            // Mail::to($recipient)->send(new CompleteOrder($order));
            event(new CompleteOrderMailEvent($order));
        } catch (Exception $e) {
            Log::warning("Email - Delivered failed: " . $e->getMessage());
        }

        // try {
        //     $this->sendPushMessage($order->user_id, ['order_id' => $orderId, 'productId' => $order->order_id], 21);
        // } catch (Exception $e) {
        //     Log::warning("Push - Delivered failed: " . $e->getMessage());
        // }
    }

    /**
     * Handle cancelled status
     */
    private function handleCancelledStatus($order, $recipient, $phone, $orderId, $user_name)
    {
        // Process refunds
        $this->processRefunds($order, new Request());

        // Send notifications
        $this->sendCancellationNotifications($order);
    }

    /**
     * Process refunds for cancelled orders
     */
    private function processRefunds($order, $request)
    {
        $payments = $order->payment ?? collect();
        $walletUsed = $order->wallet_used ?? 0;
        $refundWalletAmount = $walletUsed;
        $refundOnlineAmount = max(0, $order->total - $walletUsed);


        // 2. Online refund (Razorpay)
        if ($refundOnlineAmount > 0) {
            $onlinePayment = $payments
                ->where('payment_type', 'Online')
                ->where('payment_status', 'Success')
                ->where('order_id', $order->order_id)
                ->first();

            if ($onlinePayment) {
                try {
                    $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

                    $refund = $api->payment->fetch($onlinePayment->payment_id)
                        ->refund(['amount' => $refundOnlineAmount * 100]);

                    OrderRefund::create([
                        'order_id' => $order->order_id,
                        'payment_id' => $onlinePayment->payment_id,
                        'refund_id' => $refund->id ?? null,
                        'amount' => $refundOnlineAmount,
                        'payment_method' => 'razorpay',
                        'status' => $refund->status ?? 'initiated',
                        'refunded_at' => now(),
                        'created_by' => auth()->id()
                    ]);
                } catch (\Exception $e) {
                    if (!str_contains($e->getMessage(), 'already')) {
                        Log::error("Refund error for order {$order->order_id}: " . $e->getMessage());
                        throw $e;
                    }
                }
            }
        }

        // 3. COD order - only record cancellation
        $hasOnlyCOD = $payments->isEmpty() || $payments->where('payment_type', 'Online')->count() == 0;

        if ($hasOnlyCOD) {
            CancelledOrder::create([
                'order_id' => $order->order_id,
                'user_id' => auth()->id(),
                'comment' => $request->cancel_reason ?? '',
                'amount' => $order->total,
                'is_refunded' => 'yes',
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Send cancellation notifications
     */
    private function sendCancellationNotifications($order)
    {
        $order->load([
            'user',
            'cancel_detail',
            'payment',
            'refunds',
            'address',
            'items'
        ]);

        $recipient = $order->user->email ?? 'info@ammasspices.com ';
        $phone = $order->address->receiver_phone ?? $order->user->user_phone;
        $orderId = $order->order_number;
        $user_name = $order->user->first_name ?? $order->user->display_name;

        Mail::to($recipient)->send(new CancelOrder($order, $recipient));

        // $this->sendPushMessage($order->user_id, [
        //     'order_id' => $orderId,
        //     'productId' => $order->order_id
        // ], 27);

        // $this->sendSmsSafe($phone, "Your order {$orderId} has been cancelled. We regret the inconvenience. - MISS25");
        // $this->sendSmsSafe($phone, "Dear {$user_name}, your refund of ₹{$order->total} has been initiated for Order {$orderId}. It will reflect in 5–7 working days. - MISSTF");
    }

    /**
     * Send SMS safely with error handling
     */
    protected function sendSmsSafe($phone, $message)
    {
        if ($phone) {
            try {
                $this->sendSms([
                    'phone' => $phone,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                Log::warning("SMS send failed: " . $e->getMessage());
            }
        }
    }

    public function getOrders()
    {
        $shiprocket = new ShiprocketService();
        return $shiprocket->getOrders();
    }
}
