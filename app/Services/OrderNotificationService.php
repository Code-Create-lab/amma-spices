<?php

namespace App\Services;

use App\Models\Orders;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\OrderPlacedEmailJob;

class OrderNotificationService
{

    protected SmsService $smsService;
    protected WhatsAppService $whatsAppService;


    public function __construct(SmsService $smsService, WhatsAppService $whatsAppService)
    {
        $this->smsService = $smsService;
        $this->whatsAppService = $whatsAppService;
    }
    /**
     * Send Order Success SMS + WhatsApp
     */
    public function sendOrderSuccessSMS($mobile, $name, $orderNumber, $amount, $order)
    {

        try {
            // Construct the message based on whether order number is provided

            $message = "Thank you for your order! Order #{$orderNumber} for Rs {$amount} has been placed successfully. -Amma's Spices"; // Changed from 'message' to 'text'
            $response = $this->smsService->sendSms('91' . $mobile, $message, "1707176847344339122");




            $AdminMessage = "Order Alert: {$orderNumber} placed by {$name} for Rs {$amount}. Login to process -Amma's Spices"; // Changed from 'message' to 'text'
            $AdminResponse = $this->smsService->sendSms('919008741100', $AdminMessage, "1707176848125331222");


            // $optionsOrder = [
            //     'clientId' => 'zakh_bot',
            //     'msg_type' => 'IMAGE',
            //     'header' => '',
            //     'footer' => 'ZAKHEC',
            //     'buttonUrlParam' => null,
            //     'button' => 'false',
            //     'media_url' => "https://zakh.in/storage/" . $order->orderItems[0]->varient_image,
            //     "lang" => "en",
            //     "msg" => null,
            //     "userName" => null,
            //     "parametersArrayObj" => null,
            //     "headerParam" => null
            // ];
            // $whatsappOrder = $this->whatsAppService->send('91' . $mobile, 'order', [$order->cart_id, $order->total_price],  $optionsOrder);


            // Log::info('Whatsapp User Order SMS sent successfully', [
            //     'whatsappResponse' => $whatsappOrder,
            // ]);

            // $optionsOrderAlert = [
            //     'clientId' => 'zakh_bot',
            //     'msg_type' => 'IMAGE',
            //     'header' => '',
            //     'footer' => 'ZAKHEC',
            //     'buttonUrlParam' => null,
            //     'button' => 'false',
            //     'media_url' => "https://zakh.in/storage/" . $order->orderItems[0]->varient_image,
            //     "lang" => "en",
            //     "msg" => null,
            //     "userName" => null,
            //     "parametersArrayObj" => null,
            //     "headerParam" => null
            // ];
            // $whatsappOrder = $this->whatsAppService->send('919910336595', 'order_alert_2', ["#$orderNumber", "$name", $amount],  $optionsOrderAlert);


            // Log::info('Whatsapp Order Alert SMS sent successfully', [
            //     'whatsappResponse' => $whatsappOrder,
            // ]);



            if ($response) {
                Log::info('SMS sent successfully', [
                    'mobile' => $mobile,
                    'order_number' => $orderNumber,
                    'response' => $response,
                    'admin_response' => $AdminResponse,
                ]);

                // Log::info('Whatsapp Order Alert SMS sent successfully', [
                //     'whatsappResponse' => $whatsappOrder,
                // ]);

                // Log::info('Whatsapp User Order SMS sent successfully', [
                //     'whatsappResponse' => $whatsappOrder,
                // ]);



                return true;
            } else {
                Log::error('SMS sending failed', [
                    'mobile' => $mobile,
                    'order_number' => $orderNumber,
                    'response' => $response,
                    'admin_response' => $AdminResponse,
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('SMS API Error: ' . $e->getMessage(), [
                'mobile' => $mobile,
                'order_number' => $orderNumber
            ]);
            return false;
        }
    }

    /**
     * Main entry point (used by Livewire / Controller)
     */
    public function handleOrderSuccess(int $orderId)
    {
        $order = Orders::with([
            'orderItems.variation.variation_attributes.attribute.attribute',
            'orderItems.variation.product',
            'address',
            'coupon',
            'user'
        ])->findOrFail($orderId);

        $payment = Payment::where('order_id', $orderId)->latest()->first();

        if (
            strtolower($order->payment_method) === 'cod' ||
            (strtolower($order->payment_method) !== 'cod' && strtolower($order->payment_status) === 'paid')
        ) {
            $this->sendOrderSuccessSMS(
                $order->address->receiver_phone,
                $order->address->receiver_name,
                $order->cart_id,
                $order->total_price,
                $order
            );
        }

        // ✅ EMAIL IS QUEUED
        OrderPlacedEmailJob::dispatch($order);

        Log::info('Order notifications triggered', [
            'order_id' => $orderId,
            'user_email' => $order->user->email,
        ]);

        return true;
    }


    public function sendOrderNotifications(int $orderId)
    {
        $order = Orders::with(['orderItems', 'address', 'user'])->findOrFail($orderId);

        $this->sendOrderSuccessSMS(
            $order->address->receiver_phone,
            $order->address->receiver_name,
            $order->cart_id,
            $order->total_price,
            $order
        );
    }
}
