<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send WhatsApp message via HelloYubo API
     *
     * @param string $phoneNumber
     * @param string $templateName
     * @param array $parameters
     * @param array $options
     * @return array
     */
    public function send(string $phoneNumber, string $templateName, array $parameters = [], array $options = []): array
    {
        try {
            $payload = [
                'clientId' => $options['clientId'] ?? 'zakh_bot',
                'channel' => 'whatsapp',
                'send_to' => $this->formatPhoneNumber($phoneNumber),
                'templateName' => $templateName,
                'parameters' => $parameters,
                'msg_type' => $options['msg_type'] ?? 'TEXT',
                'header' => $options['header'] ?? '',
                'footer' => $options['footer'] ?? 'ZAKHEC',
                'buttonUrlParam' => $options['buttonUrlParam'] ?? null,
                'button' => $options['button'] ?? 'false',
                'media_url' => $options['media_url'] ?? '',
                'lang' => $options['lang'] ?? 'en',
                'msg' => $options['msg'] ?? null,
                'userName' => $options['userName'] ?? null,
                'parametersArrayObj' => $options['parametersArrayObj'] ?? null,
                'headerParam' => $options['headerParam'] ?? null,
            ];

            $response = Http::timeout(30)
                ->retry(2, 100)
                ->withToken(config('services.whatsapp.api_token'))
                ->post('https://api.helloyubo.com/v3/whatsapp/notification', $payload);

            Log::info('WhatsApp message sent successfully', [
                'number' => $payload['send_to'],
                'template' => $templateName,
                'response' => $response->json()
            ]);

            return [
                'success' => true,
                'response' => $response->json(),
                'status_code' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp sending failed', [
                'number' => $phoneNumber,
                'template' => $templateName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send order cancellation WhatsApp
     *
     * @param object $order
     * @return array
     */
    public function sendOrderCancellationWhatsApp($order): array
    {
        $phoneNumber = $order->user->user_phone;

        if (!$phoneNumber) {
            return [
                'success' => false,
                'error' => 'Phone number not found'
            ];
        }

        $firstItem = $order->orderItems->first();
        $hasImage = $firstItem && !empty($firstItem->varient_image);

        $parameters = [$order->cart_id, $order->total_price];

        if ($hasImage) {
            // With Image
            $options = [
                'msg_type' => 'IMAGE',
                'footer' => 'ZAKHEC',
                'media_url' => 'https://zakh.in/storage/' . $firstItem->varient_image,
                'clientId' => 'zakh_bot',
                'header' => '',
                'buttonUrlParam' => null,
                'button' => 'false',
                'lang' => 'en',
                'msg' => null,
                'userName' => null,
                'parametersArrayObj' => null,
                'headerParam' => null,
            ];
            $templateName = 'order_canceled_2';
        } else {
            // Without Image
            $options = [
                'msg_type' => 'TEXT',
                'footer' => '-ZAKHEC',
                'media_url' => '',
                'clientId' => 'zakh_bot',
                'header' => '',
                'buttonUrlParam' => null,
                'button' => 'false',
                'lang' => 'en',
                'msg' => null,
                'userName' => null,
                'parametersArrayObj' => null,
                'headerParam' => null,
            ];
            $templateName = 'order_canceled';
        }

        return $this->send($phoneNumber, $templateName, $parameters, $options);
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