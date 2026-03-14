<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ShiprocketService
{
    protected $baseUrl = 'https://apiv2.shiprocket.in/v1/external/';
    protected $token;

    public function __construct()
    {
        // Ideally cache this token (valid for 24 hours)
        $this->token = cache()->remember('shiprocket_auth_token', now()->addHours(23), function () {
            $response = Http::post($this->baseUrl . 'auth/login', [
                'email' => config('services.shiprocket.email'),
                'password' => config('services.shiprocket.password'),
            ]);

            // dd(config('services.shiprocket.email'), config('services.shiprocket.password'), $response->body(),$this->baseUrl . 'auth/login');
            if (! $response->successful()) {
                Log::error('Shiprocket Auth Failed', ['response' => $response->body()]);
                throw new \Exception("Shiprocket auth failed: " . $response->body());
            }

            $token = $response->json()['token'] ?? null;

            if (! $token) {
                throw new \Exception('Shiprocket token missing in response');
            }

            return $token;
        });
    }


    private function headers()
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Get serviceability and rates for delivery
     * 
     * @param array $params [
     *     'pickup_postcode' => '110001',
     *     'delivery_postcode' => '400001',
     *     'weight' => 500, // in grams
     *     'cod' => 1, // 1 for COD, 0 for Prepaid
     *     'declared_value' => 1000, // order value
     * ]
     * @return array|null
     */


    public function trackByAwb(string $awb): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->get("{$this->baseUrl}courier/track/awb/{$awb}");

            Log::info('Shiprocket tracking response', [
                'status' => $response->status(),
                'awb' => $awb,
                'body' => $response->body(),
            ]);

            // Parse response
            $data = $response->json();

            // Handle different status codes
            if ($response->status() === 200 && isset($data['tracking_data'])) {
                // Success
                return $data;
            } elseif ($response->status() === 500) {
                // Server error (could be cancelled AWB)
                return [
                    'error_id' => $data['error_id'] ?? 'unknown',
                    'message' => $data['message'] ?? 'Server error occurred',
                    'status_code' => 500,
                ];
            } elseif ($response->status() === 404) {
                // Not found
                return [
                    'message' => 'AWB not found in Shiprocket system',
                    'status_code' => 404,
                ];
            } else {
                // Other errors
                return [
                    'message' => $data['message'] ?? 'Unknown error occurred',
                    'status_code' => $response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Shiprocket API exception', [
                'awb' => $awb,
                'error' => $e->getMessage()
            ]);

            return [
                'message' => 'Connection error: ' . $e->getMessage(),
                'status_code' => 0,
            ];
        }
    }

    public function getServiceabilityRates(array $params)
    {
        try {
            Log::info('shiprocket header', ['header' => $this->headers(), 'params' => $params]);

            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}courier/serviceability", $params);

            if ($response->successful()) {
                return $response->json();
            }

            // If unauthorized, try to re-authenticate
            if ($response->status() === 401) {
                Cache::forget('shiprocket_auth_token');

                // dd($this->baseUrl . "/courier/serviceability", $params);

                // Retry the request
                $response = Http::withHeaders($this->headers())
                    ->get("{$this->baseUrl}courier/serviceability", $params);
                if ($response->successful()) {
                    return $response->json();
                }
            }

            Log::error('Shiprocket serviceability check failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'params' => $params
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Shiprocket serviceability exception: ' . $e->getMessage(), [
                'params' => $params
            ]);
            return null;
        }
    }


    public function createOrder($order)
    {
        $payload = [
            "order_id" => $order->order_id,   // from your form: order no
            "order_date" => $order->order_date,   // date
            'pickup_location' => "Primary",
            //"pickup_location" => $shipment['add'], // OR use your mapped pickup; you can replace with fixed "work"

            // Billing / Shipping (Your form → Shiprocket format)
            "billing_customer_name" => $order->address->receiver_name,
            "billing_last_name" => "",
            "billing_address" => trim($order->address->house_no . " " . $order->address->society),
            "billing_city" => $order->address->city,
            "billing_pincode" => $order->address->pincode,
            "billing_state" => $order->address->state,
            "billing_country" => "India",
            "billing_email" => $order->address->receiver_email,
            "billing_phone" => $order->address->receiver_phone,

            "shipping_is_billing" => true,

            // Items Mapping
            "order_items" => $order->orderItems->map(function ($p) {
                return [
                    "name"          => $p->product_name,
                    "sku"           => optional($p->variation->product)->hsn_number ?? "",
                    "units"         => $p->quantity ?? 1,
                    "selling_price" => $p->price ?? 0,
                ];
            })->toArray(),

            // Payment
            "payment_method" =>  $order->payment_method == 'COD' ? "COD" : "Prepaid",
            "sub_total" => (($order->total_price - $order->delivery_charge) + $order->coupon_discount)  ?? 0,

            // Dimensions
            "length" => $order->length,
            "breadth" => $order->breadth,
            "height" => $order->height,
            "weight" => $order->weight,
            "shipping_charges" => $order->delivery_charge ?? 0,
            "giftwrap_charges" => 0,
            // "transaction_charges"=> 0,
            "total_discount" => $order->coupon_discount ?? 0
        ];

        // dd($payload);
        Log::info('payload of shiprocket:', ['payload' => $payload]);
        Log::info('api url of shiprocket:', ['url' => $this->baseUrl . 'orders/create/adhoc']);

        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . 'orders/create/adhoc', $payload);

        Log::info('response of shiprocket order create:', ['response' => $response]);

        return $response->json();
    }

    public function trackOrder($shipmentId)
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->baseUrl . 'courier/track/shipment/' . $shipmentId);
        // dd($shipmentId,$response->json());
        return $response->json();
    }



    public function getOrders()
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->baseUrl . 'orders');

        return $response->json();
    }

    public function assignCourier($shipmentId, $courierId)
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . 'courier/assign/awb', [
                'shipment_id' => [$shipmentId],
                'courier_id' => (int) $courierId
            ]);


        Log::info('Shiprocket assignCourier response', $response->json());

        return $response->json();
    }

    public function shipOrder($shipmentId)
    {
        Log::info('Shiprocket generate pickup response called', ['shipment_id' => $shipmentId]);
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . 'courier/generate/pickup', [
                'shipment_id' => [$shipmentId]
            ]);

        Log::info('Shiprocket generate pickup response', $response->json());

        return $response->json();
    }

    public function generateManifest($shipmentId)
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . 'manifests/generate', [
                'shipment_id' => [$shipmentId]
            ]);

        Log::info('Shiprocket generateManifest response', $response->json());

        return $response->json();
    }

    public function generateLabel($shipmentId)
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . 'courier/generate/label', [
                'shipment_id' => [$shipmentId]
            ]);

        Log::info('Shiprocket generateLabel response', $response->json());

        return $response->json();
    }

    /**
     * Generate Invoice (returns invoice PDF URL)
     */
    public function generateInvoice($orderId)
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . 'orders/print/invoice', [
                'ids' => [$orderId]
            ]);

        Log::info('Shiprocket Generate Invoice Response', $response->json());

        return $response->json();
    }

    public function cancelOrder($orderId)
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . 'orders/cancel', [
                'ids' => [$orderId]
            ]);

        Log::info('Shiprocket cancelOrder response:', $response->json());
        return $response->json();
    }

    public function cancelShipment($shipmentId)
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . 'orders/cancel/shipment/awbs', [
                'awbs' => [$shipmentId]
            ]);

        Log::info('Shiprocket cancelShipment response:', $response->json());
        return $response->json();
    }


    /**
     * Initiate Return to Origin (RTO) for a shipped order
     * Use this when customer wants to return/cancel an already shipped order
     * 
     * @param string $awbCode - The AWB/tracking number of the shipment
     * @return array Response from Shiprocket API
     * @throws \Exception
     */
    public function initiateRTO($awbCode)
    {
        try {

            $response = Http::withHeaders($this->headers())
                ->post($this->baseUrl . '/api/v1/orders/cancel/shipment/awbs', [
                    'awbs' => [$awbCode]
                ]);

            if ($response->successful()) {
                Log::info('RTO initiated successfully', [
                    'awb_code' => $awbCode,
                    'response' => $response->json()
                ]);

                return $response->json();
            }

            Log::error('RTO initiation failed', [
                'awb_code' => $awbCode,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            throw new \Exception('Failed to initiate RTO: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('RTO initiation exception', [
                'awb_code' => $awbCode,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Alternative method - Request pickup for return
     * Use this if you want to schedule a pickup for returned products
     * 
     * @param int $orderId - Shiprocket order ID
     * @return array Response from Shiprocket API
     */
    public function scheduleReturnPickup($orderId)
    {
        try {

            $response = Http::withHeaders($this->headers())
                ->post($this->baseUrl . '/api/v1/orders/create/return', [
                    'order_id' => $orderId
                ]);

            if ($response->successful()) {
                Log::info('Return pickup scheduled successfully', [
                    'order_id' => $orderId,
                    'response' => $response->json()
                ]);

                return $response->json();
            }

            Log::error('Return pickup scheduling failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            throw new \Exception('Failed to schedule return pickup: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Return pickup scheduling exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get shipment tracking details to check RTO status
     * 
     * @param string $awbCode
     * @return array Tracking information
     */
    public function getShipmentTracking($awbCode)
    {
        try {

            $response = Http::withHeaders($this->headers())
                ->get($this->baseUrl . '/api/v1/courier/track/awb/' . $awbCode);


            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('Failed to get tracking details: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Tracking fetch exception', [
                'awb_code' => $awbCode,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
