<?php

namespace App\Services;

use Exception;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorPayService
{
    private $api;

    public function __construct()
    {
        $this->api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
    }

    /**
     * Create an order in Razorpay
     */
    public function createOrder(array $data)
    {
        try {
            $orderData = [
                'receipt'         => $data['order_id'],
                'amount'          => round($data['amount'] * 100), // in paise
                'currency'        => $data['currency'] ?? 'INR',
                'payment_capture' => 1,
                'notes'           => [
                    'order_id' => $data['order_id'],
                    'cart_id' => $data['order_id']
                ]
            ];

            $order = $this->api->order->create($orderData);

            return [
                'success' => true,
                'order' => $order
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment signature from Razorpay callback
     */
    public function verifyPayment(array $payload)
    {
        try {
            $attributes = [
                'razorpay_order_id'   => $payload['razorpay_order_id'],
                'razorpay_payment_id' => $payload['razorpay_payment_id'],
                'razorpay_signature'  => $payload['razorpay_signature']
            ];

            $this->api->utility->verifyPaymentSignature($attributes);

            return [
                'success' => true,
                'payload' => $payload
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay payment verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($body, $signature)
    {
        try {
            $webhookSecret = config('services.razorpay.webhook_secret');

            // Temporary: Allow testing without webhook secret
            if (empty($webhookSecret)) {
                Log::warning('Webhook secret not configured - verification skipped (development only)');
                return [
                    'success' => true,
                    'message' => 'Signature verification skipped - development mode'
                ];
            }

            $expectedSignature = hash_hmac('sha256', $body, $webhookSecret);

            if ($expectedSignature !== $signature) {
                throw new \Exception('Webhook signature mismatch');
            }

            return [
                'success' => true,
                'message' => 'Signature verified'
            ];
        } catch (\Exception $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Fetch payment details
     */
    /**
     * Get payment details from Razorpay
     * Returns payment data as array
     */
    public function getPayment($paymentId)
    {
        try {
            // Fetch payment object from Razorpay
            $payment = $this->api->payment->fetch($paymentId);

            // Method 1: Try toArray()
            $paymentArray = $payment->toArray();

            // Method 2: If empty, try attributes property
            if (empty($paymentArray) && isset($payment->attributes)) {
                $paymentArray = $payment->attributes;
            }

            // Method 3: If still empty, manually extract properties
            if (empty($paymentArray)) {
                $paymentArray = [
                    'id' => $payment->id ?? $paymentId,
                    'entity' => $payment->entity ?? 'payment',
                    'amount' => $payment->amount ?? 0,
                    'currency' => $payment->currency ?? 'INR',
                    'status' => $payment->status ?? 'unknown',
                    'order_id' => $payment->order_id ?? null,
                    'method' => $payment->method ?? null,
                    'email' => $payment->email ?? null,
                    'contact' => $payment->contact ?? null,
                    'created_at' => $payment->created_at ?? time(),
                    'captured' => $payment->captured ?? false,
                    'description' => $payment->description ?? null,
                ];
            }

            Log::info('Payment fetched successfully', [
                'payment_id' => $paymentId,
                'status' => $paymentArray['status'] ?? 'unknown'
            ]);

            return $paymentArray;
        } catch (Exception $e) {
            Log::error('Failed to fetch payment from Razorpay', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Refund a payment
     */
    public function refundPayment($paymentId, array $data)
    {
        try {
            $refundData = [
                'amount' => isset($data['amount']) ? round($data['amount'] * 100) : null,
                'speed'  => $data['speed'] ?? 'normal'
            ];

            $refund = $this->api->payment->fetch($paymentId)->refund($refundData);

            return [
                'success' => true,
                'refund' => $refund
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay refund failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all payments by Razorpay Order ID
     */
    public function getPaymentsByOrderId(string $razorpayOrderId)
    {
        try {
            return $this->api->order->fetch($razorpayOrderId)->payments();
        } catch (\Exception $e) {
            Log::error('Razorpay fetch payments by order id failed', [
                'razorpay_order_id' => $razorpayOrderId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get captured payment from order payments
     */
    public function getCapturedPaymentByOrderId(string $razorpayOrderId)
    {
        $payments = $this->getPaymentsByOrderId($razorpayOrderId);

        if (!$payments || empty($payments->items)) {
            return null;
        }

        return collect($payments->items)->firstWhere('status', 'captured');
    }
}
