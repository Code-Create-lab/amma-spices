<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS via Admagister API
     *
     * @param string $phoneNumber
     * @param string $message
     * @param string|null $templateId
     * @return array
     */
    public function sendSms(string $phoneNumber, string $message, ?string $templateId = null): array
    {
        try {
            $params = [
                'user' => config('services.sms.user', 'zakh'),
                'password' => config('services.sms.password'),
                'senderid' => config('services.sms.sender_id', 'ZAKHEC'),
                'channel' => 'Trans',
                'DCS' => 0,
                'flashsms' => 0,
                'route ' => 42,
                'number' => $this->formatPhoneNumber($phoneNumber),
                'text' => $message,
                'Peid' => config('services.sms.peid', '1701176414187427388'),
                'DLTTemplateId' => $templateId ?? config('services.sms.default_template_id', '1707176361958332349')
            ];

            $response = Http::timeout(30)
                ->retry(2, 100);
                // ->get('http://www.admagister.net/api/mt/SendSMS', $params);

            Log::info('SMS sent successfully', [
                'number' => $params['number'],
                'response' => $response->json()
            ]);

            return [
                'success' => true,
                'response' => $response->json(),
                'status_code' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'number' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send order cancellation SMS
     *
     * @param object $order
     * @return array
     */
    public function sendOrderCancellationSms($order): array
    {
        // Get phone number with priority
        $phoneNumber = $order->address->receiver_phone ?? $order->user->user_phone;

        if (!$phoneNumber) {
            return [
                'success' => false,
                'error' => 'Phone number not found'
            ];
        }

        // Build message
        $message = "Your order #{$order->cart_id} for Rs.{$order->total_price} has been canceled as it could not be confirmed. In case you paid online, your amount will be refunded within 5-7 business days. You can place a new order anytime. - ZAKHEC";

        // Send SMS
        return $this->sendSms($phoneNumber, $message, '1707176361958332349');
    }

    /**
     * Format phone number with country code
     *
     * @param string $phoneNumber
     * @return string
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Add country code if not present
        if (!str_starts_with($phone, '91')) {
            $phone = '91' . $phone;
        }

        return $phone;
    }
}
