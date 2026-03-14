<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class IThinkLogisticsService
{
    protected $baseUrl;
    protected $accessToken;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ithinklogistics.base_url');
        $this->accessToken = config('services.ithinklogistics.access_token');
        $this->secretKey = config('services.ithinklogistics.secret_key');
    }

    /**
     * Create a new shipping order
     *
     * @param array $orderData
     * @return array
     * @throws Exception
     */
    public function createOrder(array $orderData)
    {

        try {
            $payload = $this->prepareOrderPayload($orderData);

              return $payload;
            dd($this->accessToken ,$payload);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ])
                ->post($this->baseUrl . '/order/add.json', $payload);

            if ($response->failed()) {
                Log::error('iThinkLogistics API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                throw new Exception('Failed to create shipping order: ' . $response->body());
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('iThinkLogistics Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Prepare the order payload
     *
     * @param array $orderData
     * @return array
     */
    protected function prepareOrderPayload(array $orderData)
    {
        return [
            'data' => [
                'shipments' => [
                    [
                        'waybill' => $orderData['waybill'] ?? '',
                        'order' => $orderData['order_id'],
                        'sub_order' => $orderData['sub_order'] ?? '',
                        'order_date' => $orderData['order_date'],
                        'total_amount' => $orderData['total_amount'],
                        
                        // Shipping Information
                        'name' => $orderData['name'],
                        'company_name' => $orderData['company_name'] ?? '',
                        'add' => $orderData['address'],
                        'add2' => $orderData['address_2'] ?? '',
                        'add3' => $orderData['address_3'] ?? '',
                        'pin' => $orderData['pincode'],
                        'city' => $orderData['city'],
                        'state' => $orderData['state'],
                        'country' => $orderData['country'] ?? 'India',
                        'phone' => $orderData['phone'],
                        'alt_phone' => $orderData['alt_phone'] ?? '',
                        'email' => $orderData['email'],
                        
                        // Billing Information
                        'is_billing_same_as_shipping' => $orderData['is_billing_same'] ?? 'yes',
                        'billing_name' => $orderData['billing_name'] ?? $orderData['name'],
                        'billing_company_name' => $orderData['billing_company'] ?? '',
                        'billing_add' => $orderData['billing_address'] ?? $orderData['address'],
                        'billing_add2' => $orderData['billing_address_2'] ?? '',
                        'billing_add3' => $orderData['billing_address_3'] ?? '',
                        'billing_pin' => $orderData['billing_pincode'] ?? $orderData['pincode'],
                        'billing_city' => $orderData['billing_city'] ?? $orderData['city'],
                        'billing_state' => $orderData['billing_state'] ?? $orderData['state'],
                        'billing_country' => $orderData['billing_country'] ?? 'India',
                        'billing_phone' => $orderData['billing_phone'] ?? $orderData['phone'],
                        'billing_alt_phone' => $orderData['billing_alt_phone'] ?? '',
                        'billing_email' => $orderData['billing_email'] ?? $orderData['email'],
                        
                        // Products
                        'products' => $this->formatProducts($orderData['products']),
                        
                        // Shipment Details
                        'shipment_length' => $orderData['length'] ?? '10',
                        'shipment_width' => $orderData['width'] ?? '10',
                        'shipment_height' => $orderData['height'] ?? '5',
                        'weight' => $orderData['weight'],
                        
                        // Charges
                        'shipping_charges' => $orderData['shipping_charges'] ?? '0',
                        'giftwrap_charges' => $orderData['giftwrap_charges'] ?? '0',
                        'transaction_charges' => $orderData['transaction_charges'] ?? '0',
                        'total_discount' => $orderData['total_discount'] ?? '0',
                        'first_attemp_discount' => $orderData['first_attempt_discount'] ?? '0',
                        
                        // Payment
                        'cod_amount' => $orderData['cod_amount'] ?? '0',
                        'payment_mode' => $orderData['payment_mode'],
                        
                        // Additional
                        'reseller_name' => $orderData['reseller_name'] ?? '',
                        'eway_bill_number' => $orderData['eway_bill'] ?? '',
                        'gst_number' => $orderData['gst_number'] ?? '',
                        'what3words' => $orderData['what3words'] ?? '',
                        'return_address_id' => $orderData['return_address_id'],
                    ]
                ],
                'pickup_address_id' => $orderData['pickup_address_id'],
                'access_token' => $this->accessToken,
                'secret_key' => $this->secretKey,
                'logistics' => $orderData['logistics'] ?? 'fedex',
                's_type' => $orderData['service_type'] ?? 'ground',
                'order_type' => $orderData['order_type'] ?? '',
            ]
        ];
    }

    /**
     * Format products for the API
     *
     * @param array $products
     * @return array
     */
    protected function formatProducts(array $products)
    {
        return array_map(function ($product) {
            return [
                'product_name' => $product['name'],
                'product_sku' => $product['sku'],
                'product_quantity' => $product['quantity'],
                'product_price' => $product['price'],
                'product_tax_rate' => $product['tax_rate'] ?? '0',
                'product_hsn_code' => $product['hsn_code'] ?? '',
                'product_discount' => $product['discount'] ?? '0',
                'product_img_url' => $product['image_url'] ?? '',
            ];
        }, $products);
    }

    /**
     * Track shipment by waybill
     *
     * @param string $waybill
     * @return array
     */
    public function trackShipment(string $waybill)
    {
        try {
            $response = Http::timeout(30)
                ->post($this->baseUrl . '/order/track.json', [
                    'data' => [
                        'awb_number_list' => $waybill,
                        'access_token' => $this->accessToken,
                        'secret_key' => $this->secretKey,
                    ]
                ]);

            if ($response->failed()) {
                throw new Exception('Failed to track shipment: ' . $response->body());
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('iThinkLogistics Tracking Error', [
                'waybill' => $waybill,
                'message' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Cancel shipment
     *
     * @param string $waybill
     * @return array
     */
    public function cancelShipment(string $waybill)
    {
        try {
            $response = Http::timeout(30)
                ->post($this->baseUrl . '/order/cancel.json', [
                    'data' => [
                        'awb_numbers' => [$waybill],
                        'access_token' => $this->accessToken,
                        'secret_key' => $this->secretKey,
                    ]
                ]);

            if ($response->failed()) {
                throw new Exception('Failed to cancel shipment: ' . $response->body());
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('iThinkLogistics Cancellation Error', [
                'waybill' => $waybill,
                'message' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}