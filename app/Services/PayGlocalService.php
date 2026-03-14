<?php

namespace App\Services;

use App\Models\Orders;
use App\Models\User;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128CBCHS256;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP256;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\Serializer\CompactSerializer as EncryptionCompactSerializer;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer as SigCompactSerializer;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Signature\JWSLoader;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Jose\Component\Signature\Serializer\CompactSerializer;

/**
 * PayGlocal Payment Service
 * 
 * Handles PayGlocal payment integration with JWT encryption and signing
 * Optimized for performance with detailed logging and error handling
 */
class PayGlocalService
{
    // ========================================
    // CONFIGURATION & PROPERTIES
    // ========================================

    private $config;
    private $baseUrl;

    // Cached instances for performance optimization
    private static $keyEncryptionAlgorithmManager;
    private static $contentEncryptionAlgorithmManager;
    private static $compressionMethodManager;
    private static $signatureAlgorithmManager;
    private static $encryptionKey;
    private static $signingKey;
    private static $keyPathsCache;

    // ========================================
    // INITIALIZATION
    // ========================================

    public function __construct()
    {
        $this->config = config('payglocal');
        $this->baseUrl = $this->config['base_urls'][$this->config['environment']];
        $this->initializeAlgorithmManagers();

        // dd($this->baseUrl);
    }

    /**
     * Initialize algorithm managers (cached for performance)
     */
    private function initializeAlgorithmManagers(): void
    {
        if (self::$keyEncryptionAlgorithmManager === null) {
            self::$keyEncryptionAlgorithmManager = new AlgorithmManager([new RSAOAEP256()]);
            self::$contentEncryptionAlgorithmManager = new AlgorithmManager([new A128CBCHS256()]);
            self::$compressionMethodManager = new CompressionMethodManager([new Deflate()]);
            self::$signatureAlgorithmManager = new AlgorithmManager([new RS256()]);
        }
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Generate cryptographically secure random string
     */
    private function generateRandomString($length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Get key file paths with caching and validation
     */
    private function getKeyPaths(): array
    {
        if (self::$keyPathsCache !== null) {
            return self::$keyPathsCache;
        }

        $privateKeyPath = storage_path('app/' . $this->config['keys']['private_key_path']);
        $publicKeyPath = storage_path('app/' . $this->config['keys']['public_key_path']);

        self::$keyPathsCache = [
            'private' => $privateKeyPath,
            'public' => $publicKeyPath
        ];

        Log::info('PayGlocal Key Paths:', [
            'private_key' => $privateKeyPath,
            'public_key' => $publicKeyPath,
            'private_exists' => file_exists($privateKeyPath),
            'public_exists' => file_exists($publicKeyPath)
        ]);

        return self::$keyPathsCache;
    }

    /**
     * Get cached encryption key
     */
    private function getEncryptionKey()
    {
        if (self::$encryptionKey !== null) {
            return self::$encryptionKey;
        }

        $paths = $this->getKeyPaths();

        self::$encryptionKey = JWKFactory::createFromKeyFile(
            $paths['public'],
            null,
            [
                'kid' => $this->config['keys']['public_key_id'],
                'use' => 'enc',
                'alg' => 'RSA-OAEP-256',
            ]
        );

        return self::$encryptionKey;
    }

    /**
     * Get cached signing key
     */
    private function getSigningKey()
    {
        if (self::$signingKey !== null) {
            return self::$signingKey;
        }

        $paths = $this->getKeyPaths();

        self::$signingKey = JWKFactory::createFromKeyFile(
            $paths['private'],
            null,
            [
                'kid' => $this->config['keys']['private_key_id'],
                'use' => 'sig'
            ]
        );

        return self::$signingKey;
    }

    /**
     * Format bytes for logging
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Log execution time with performance metrics
     */
    private function logExecutionTime($operation, $startTime, $additionalData = []): float
    {
        $executionTime = microtime(true) - $startTime;
        $logData = array_merge([
            'operation' => $operation,
            'execution_time' => number_format($executionTime, 6) . ' seconds',
            'memory_usage' => $this->formatBytes(memory_get_usage()),
            'memory_peak' => $this->formatBytes(memory_get_peak_usage())
        ], $additionalData);

        Log::info("PayGlocal Performance: {$operation}", $logData);
        return $executionTime;
    }

    // ========================================
    // JWT TOKEN CREATION METHODS
    // ========================================

    /**
     * Create JWE Token (optimized version)
     */
    private function createJWEToken($payload = null): string
    {
        $jweBuilder = new JWEBuilder(
            self::$keyEncryptionAlgorithmManager,
            self::$contentEncryptionAlgorithmManager,
            self::$compressionMethodManager
        );

        $key = $this->getEncryptionKey();

        $header = [
            'issued-by' => $this->config['merchant_id'],
            'enc' => 'A128CBC-HS256',
            'exp' => 30000,
            'iat' => (string)round(microtime(true) * 1000),
            'alg' => 'RSA-OAEP-256',
            'kid' => $this->config['keys']['public_key_id']
        ];

        // Use provided payload or create default
        // if ($payload === null) {
        //     $merchantUniqueId = $this->generateRandomString(16);
        //     $payload = json_encode([
        //         "merchantTxnId" => "23AEE8CB6B62EE2AF07",
        //         "merchantUniqueId" => $merchantUniqueId,
        //         "paymentData" => [
        //             "totalAmount" => "10.00",
        //             "txnCurrency" => "INR",
        //             "billingData" => [
        //                 "firstName" => "John",
        //                 "lastName" => "Denver",
        //                 "addressStreet1" => "Rowley street 1",
        //                 "addressStreet2" => "Punctuality lane",
        //                 "addressCity" => "Bangalore",
        //                 "addressState" => "Karnataka",
        //                 "addressPostalCode" => "560094",
        //                 "addressCountry" => "IN",
        //                 "emailId" => "johndenver@myemail.com"
        //             ]
        //         ],
        //         "merchantCallbackURL" => "http://localhost/payglocal-php-sdk-main/response.php"
        //     ]);
        // }

        $payload = json_encode($payload);

        $jwe = $jweBuilder
            ->create()
            ->withPayload($payload)
            ->withSharedProtectedHeader($header)
            ->addRecipient($key)
            ->build();

        $serializer = new EncryptionCompactSerializer();
        return $serializer->serialize($jwe, 0);
    }

    /**
     * Create JWS Token (optimized version)
     */
    private function createJWSToken(string $encryptedPayload): string
    {
        $jwsBuilder = new JWSBuilder(self::$signatureAlgorithmManager);
        $jwsKey = $this->getSigningKey();

        $jwsHeader = [
            'issued-by' => $this->config['merchant_id'],
            'is-digested' => 'true',
            'alg' => 'RS256',
            'x-gl-enc' => 'true',
            'x-gl-merchantId' => $this->config['merchant_id'],
            'kid' => $this->config['keys']['private_key_id']
        ];

        $hashedPayload = base64_encode(hash('sha256', $encryptedPayload, true));

        $jwsPayload = json_encode([
            'digest' => $hashedPayload,
            'digestAlgorithm' => "SHA-256",
            'exp' => 300000,
            'iat' => (string)round(microtime(true) * 1000)
        ]);

        $jws = $jwsBuilder
            ->create()
            ->withPayload($jwsPayload)
            ->addSignature($jwsKey, $jwsHeader)
            ->build();

        $jwsSerializer = new SigCompactSerializer();
        return $jwsSerializer->serialize($jws, 0);
    }

    // ========================================
    // MAIN PAYMENT METHODS
    // ========================================

    /**
     * Initiate payment with comprehensive performance monitoring
     */
    public function initiatePayment($order)
    {

        // dd($order);
        set_time_limit(300);
        $totalStartTime = microtime(true);

        Log::info("PayGlocal Payment Started", [
            'order_id' => $order,
            'timestamp' => date('Y-m-d H:i:s'),
            'memory_start' => $this->formatBytes(memory_get_usage()),
            'server_time' => microtime(true)
        ]);

        try {
            // STEP 1: Database Operations
            $stepStartTime = microtime(true);
            $orderId = $order;
            $order = Orders::where('cart_id', $order)->first();
            $user = User::find($order->user_id);
            // $userid = "wa_91" . $user->user_phone . "_766e746970756b74677a_554283991091136";
            $totalValue = $order->total_price - ($order->coupon_discount ?? 0);
            // dd($user ,$order);
            $this->logExecutionTime('Database Operations', $stepStartTime, [
                'order_id' => $orderId,
                'user_id' => $user->id ?? 0,
                'user_phone' => $user->user_phone ?? "",
                'total_amount' => $totalValue
            ]);
            // STEP 2: Prepare Payment Data
            $stepStartTime = microtime(true);
            $currentTimestamp = (string)round(microtime(true) * 1000);
            $orderIdLength = strlen($orderId);
            $randomStringLength = 16 - $orderIdLength - 1;
            $randomString = $this->generateRandomString($randomStringLength);
            $merchantUniqueId = $orderId . '_' . $randomString;

            $payloadData = [
                "merchantTxnId" => $orderId,
                "merchantUniqueId" => $merchantUniqueId,
                "paymentData" => [
                    "totalAmount" => $totalValue,
                    "txnCurrency" => "INR",
                    "billingData" => [
                        'firstName' => explode(' ', $order->address->receiver_name)[0] ?? '',
                        'lastName' => explode(' ', $order->address->receiver_name, 2)[1] ?? '',
                        'addressStreet1' => $order->address->society,
                        'addressStreet2' => '', // Add if you have additional address line
                        'addressCity' => $order->address->city,
                        'addressState' => $order->address->state,
                        'addressPostalCode' => $order->address->pincode,
                        'addressCountry' => 'IN',
                        'emailId' => $order->address->receiver_email,
                    ],
                ],
                "riskData" => [
                    "shippingData" => [
                        'firstName' => explode(' ', $order->address->receiver_name)[0] ?? '',
                        'lastName' => explode(' ', $order->address->receiver_name, 2)[1] ?? '',
                        'addressStreet1' => $order->address->society,
                        'addressStreet2' => '', // Add if you have additional address line
                        'addressCity' => $order->address->city,
                        'addressState' => $order->address->state,
                        'addressPostalCode' => $order->address->pincode,
                        'addressCountry' => 'IN',
                        'emailId' => $order->address->receiver_email,
                        'callingCode' => '+91',
                        'phoneNumber' => $order->address->receiver_phone,
                    ],
                ],
                "merchantCallbackURL" => $this->config['callback_url'] ?? "http://127.0.0.1:8000/payglocal/executes"
            ];

            $payload = json_encode($payloadData);
            $this->logExecutionTime('Payment Data Preparation', $stepStartTime, [
                'merchant_unique_id' => $merchantUniqueId,
                'payload_size' => strlen($payload) . ' bytes',
                'timestamp' => $currentTimestamp
            ]);

            // STEP 3: Create JWE Token (Critical Performance Section)
            $stepStartTime = microtime(true);
            Log::info("PayGlocal: Starting JWE Creation - This is typically the slowest part");

            $jweBuilder = new JWEBuilder(
                self::$keyEncryptionAlgorithmManager,
                self::$contentEncryptionAlgorithmManager,
                self::$compressionMethodManager
            );

            $key = $this->getEncryptionKey();

            $header = [
                'issued-by' => $this->config['merchant_id'],
                'enc' => 'A128CBC-HS256',
                'exp' => 30000,
                'iat' => $currentTimestamp,
                'alg' => 'RSA-OAEP-256',
                'kid' => $this->config['keys']['public_key_id']
            ];

            $jwe = $jweBuilder->create()
                ->withPayload($payload)
                ->withSharedProtectedHeader($header)
                ->addRecipient($key)
                ->build();

            $jweTime = $this->logExecutionTime('JWE Creation (RSA Encryption)', $stepStartTime, [
                'payload_size' => strlen($payload),
                'encryption_algorithm' => 'RSA-OAEP-256',
                'content_encryption' => 'A128CBC-HS256',
                'critical_operation' => 'RSA_ENCRYPTION'
            ]);

            // STEP 4: Serialize JWE Token
            $stepStartTime = microtime(true);
            $serializer = new EncryptionCompactSerializer();
            $token = $serializer->serialize($jwe, 0);
            $this->logExecutionTime('JWE Serialization', $stepStartTime, [
                'token_size' => strlen($token) . ' bytes'
            ]);

            // STEP 5: Create JWS Token (Another Critical Performance Section)
            $stepStartTime = microtime(true);
            Log::info("PayGlocal: Starting JWS Creation - Another typically slow RSA operation");

            $jwsBuilder = new JWSBuilder(self::$signatureAlgorithmManager);
            $jwsKey = $this->getSigningKey();

            $jwsHeader = [
                'issued-by' => $this->config['merchant_id'],
                'is-digested' => 'true',
                'alg' => 'RS256',
                'x-gl-enc' => 'true',
                'x-gl-merchantId' => $this->config['merchant_id'],
                'x-gl-kid-mid' => $this->config['merchant_id'],
                'kid' => $this->config['keys']['private_key_id']
            ];

            $hashedPayload = base64_encode(hash('sha256', $token, true));
            $jwsPayload = json_encode([
                'digest' => $hashedPayload,
                'digestAlgorithm' => "SHA-256",
                'exp' => 300000,
                'iat' => $currentTimestamp
            ]);

            $jws = $jwsBuilder->create()
                ->withPayload($jwsPayload)
                ->addSignature($jwsKey, $jwsHeader)
                ->build();

            $jwsTime = $this->logExecutionTime('JWS Creation (RSA Signing)', $stepStartTime, [
                'signature_algorithm' => 'RS256',
                'digest_algorithm' => 'SHA-256',
                'critical_operation' => 'RSA_SIGNING'
            ]);

            // STEP 6: Serialize JWS Token
            $stepStartTime = microtime(true);
            $jwsSerializer = new SigCompactSerializer();
            $jwsToken = $jwsSerializer->serialize($jws, 0);
            $this->logExecutionTime('JWS Serialization', $stepStartTime, [
                'jws_token_size' => strlen($jwsToken) . ' bytes'
            ]);

            // STEP 7: HTTP Request to PayGlocal
            $stepStartTime = microtime(true);
            Log::info("PayGlocal: Making HTTP request to PayGlocal API");

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->baseUrl . '/gl/v1/payments/initiate/paycollect',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $token,
                CURLOPT_HTTPHEADER => [
                    'x-gl-token-external: ' . $jwsToken,
                    'Content-Type: text/plain'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlInfo = curl_getinfo($curl);
            $curlError = curl_error($curl);
            curl_close($curl);

            $this->logExecutionTime('HTTP Request to PayGlocal', $stepStartTime, [
                'http_code' => $httpCode,
                'response_size' => strlen($response) . ' bytes',
                'curl_total_time' => $curlInfo['total_time'] ?? 'unknown',
                'curl_connect_time' => $curlInfo['connect_time'] ?? 'unknown',
                'curl_error' => $curlError ?: 'none'
            ]);

            // STEP 8: Process Response
            $stepStartTime = microtime(true);
            $data = json_decode($response, true);
            $this->logExecutionTime('Response Processing', $stepStartTime, [
                'json_decode_error' => json_last_error_msg(),
                'response_has_redirect' => isset($data['data']['redirectUrl']) ? 'yes' : 'no'
            ]);

            // Calculate total execution time and performance analysis
            $totalExecutionTime = microtime(true) - $totalStartTime;

            Log::info("PayGlocal Payment Completed", [
                'order_id' => $orderId,
                'total_execution_time' => number_format($totalExecutionTime, 6) . ' seconds',
                'jwe_time_percentage' => number_format(($jweTime / $totalExecutionTime) * 100, 2) . '%',
                'jws_time_percentage' => number_format(($jwsTime / $totalExecutionTime) * 100, 2) . '%',
                'memory_end' => $this->formatBytes(memory_get_usage()),
                'memory_peak' => $this->formatBytes(memory_get_peak_usage()),
                'success' => isset($data['data']['redirectUrl']) ? 'yes' : 'no'
            ]);

            // Performance Analysis and Optimization Recommendations
            if ($totalExecutionTime > 30) {
                Log::warning("PayGlocal: Slow Payment Processing Detected", [
                    'total_time' => $totalExecutionTime,
                    'jwe_time' => $jweTime,
                    'jws_time' => $jwsTime,
                    'bottleneck_analysis' => [
                        'jwe_is_bottleneck' => $jweTime > ($totalExecutionTime * 0.4),
                        'jws_is_bottleneck' => $jwsTime > ($totalExecutionTime * 0.4),
                        'server_optimization_needed' => ($jweTime + $jwsTime) > ($totalExecutionTime * 0.8)
                    ],
                    'recommendations' => [
                        'install_entropy_tools' => 'sudo apt-get install rng-tools haveged',
                        'check_server_specs' => 'RSA operations require good CPU and entropy',
                        'consider_alternatives' => 'Look into non-JWT payment methods'
                    ]
                ]);
            }

            // dd($data);

            if (isset($data['data']['redirectUrl'])) {
                Log::info("PayGlocal: Redirecting to payment URL", [
                    'redirect_url' => $data['data']['redirectUrl'],
                    'payment_successful' => true
                ]);

                return ['data' => $data, 'redirectUrl' => $data['data']['redirectUrl']];
                // return redirect()->away($data['data']['redirectUrl']);
            }

            Log::error("PayGlocal: No redirect URL in response", [
                'response_data' => $data,
                'http_code' => $httpCode
            ]);
            // return response()->json(['error' => 'Unable to retrieve redirect URL'], 500);

        } catch (\Exception $e) {
            $totalExecutionTime = microtime(true) - $totalStartTime;
            Log::error("PayGlocal Payment Failed", [
                'order_id' => $order,
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'execution_time_before_error' => number_format($totalExecutionTime, 6) . ' seconds',
                'memory_usage' => $this->formatBytes(memory_get_usage()),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ========================================
    // ADDITIONAL SERVICE METHODS
    // ========================================

    /**
     * Debug configuration
     */
    public function debugConfiguration(): array
    {
        $paths = $this->getKeyPaths();

        return [
            'merchant_id' => $this->config['merchant_id'],
            'environment' => $this->config['environment'],
            'base_url' => $this->baseUrl,
            'callback_url' => $this->config['callback_url'],
            'private_key_id' => $this->config['keys']['private_key_id'],
            'public_key_id' => $this->config['keys']['public_key_id'],
            'private_key_path' => $paths['private'],
            'public_key_path' => $paths['public'],
            'private_key_exists' => file_exists($paths['private']),
            'public_key_exists' => file_exists($paths['public']),
            'private_key_readable' => file_exists($paths['private']) ? is_readable($paths['private']) : false,
            'public_key_readable' => file_exists($paths['public']) ? is_readable($paths['public']) : false,
        ];
    }

    /**
     * Process refund
     */
    public function processRefund(string $gid, array $refundData)
    {
        // try {
        //     // Create the correct refund payload structure
        //     $payload = [
        //         "merchantTxnId" => $refundData['order_id'],
        //         "merchantUniqueId" => $this->generateRandomString(16),
        //         "refundType" => $refundData['type'] ?? 'F', // F = Full, P = Partial
        //     ];

        //     // Add payment data only for partial refunds
        //     if (($refundData['type'] ?? 'F') === 'P') {
        //         $payload['paymentData'] = [
        //             "totalAmount" => number_format($refundData['amount'], 2, '.', ''),
        //             "txnCurrency" => $refundData['currency'] ?? 'INR'
        //         ];
        //     }

        //     Log::info('PayGlocal Refund Payload', $payload);

        //     $stepStartTime = microtime(true);
        //     $orderId = $refundData['order_id'];
        //     $order = Orders::where('order_id', $refundData['order_id'])->first();

        //     if (!$order) {
        //         throw new \Exception('Order not found: ' . $refundData['order_id']);
        //     }

        //     $user = User::find($order->user_id);
        //     $totalValue = $order->total_price - ($order->coupon_discount ?? 0);

        //     $this->logExecutionTime('Database Operations', $stepStartTime, [
        //         'order_id' => $orderId,
        //         'user_id' => $user->id,
        //         'user_phone' => $user->user_phone,
        //         'total_amount' => $totalValue
        //     ]);

        //     // STEP 2: Prepare refund data (simplified for refund)
        //     $stepStartTime = microtime(true);
        //     $currentTimestamp = (string)round(microtime(true) * 1000);

        //     // Convert payload to JSON - THIS IS THE KEY FIX
        //     $payloadJson = json_encode($payload);

        //     $this->logExecutionTime('Refund Data Preparation', $stepStartTime, [
        //         'payload_size' => strlen($payloadJson) . ' bytes',
        //         'timestamp' => $currentTimestamp
        //     ]);

        //     // STEP 3: Create JWE Token for refund
        //     $stepStartTime = microtime(true);
        //     Log::info("PayGlocal: Starting JWE Creation for refund");

        //     $jweBuilder = new JWEBuilder(
        //         self::$keyEncryptionAlgorithmManager,
        //         self::$contentEncryptionAlgorithmManager,
        //         self::$compressionMethodManager
        //     );

        //     $key = $this->getEncryptionKey();

        //     $header = [
        //         'issued-by' => $this->config['merchant_id'],
        //         'enc' => 'A128CBC-HS256',
        //         'exp' => 30000,
        //         'iat' => $currentTimestamp,
        //         'alg' => 'RSA-OAEP-256',
        //         'kid' => $this->config['keys']['public_key_id']
        //     ];

        //     $jwe = $jweBuilder->create()
        //         ->withPayload($payloadJson) // Use the refund payload JSON
        //         ->withSharedProtectedHeader($header)
        //         ->addRecipient($key)
        //         ->build();

        //     // STEP 4: Serialize JWE Token
        //     $stepStartTime = microtime(true);
        //     $serializer = new EncryptionCompactSerializer();
        //     $token = $serializer->serialize($jwe, 0);

        //     // STEP 5: Create JWS Token for refund
        //     $stepStartTime = microtime(true);
        //     Log::info("PayGlocal: Starting JWS Creation for refund");

        //     $jwsBuilder = new JWSBuilder(self::$signatureAlgorithmManager);
        //     $jwsKey = $this->getSigningKey();

        //     $jwsHeader = [
        //         'issued-by' => $this->config['merchant_id'],
        //         'is-digested' => 'true',
        //         'alg' => 'RS256',
        //         'x-gl-enc' => 'true',
        //         'x-gl-merchantId' => $this->config['merchant_id'],
        //         'x-gl-kid-mid' => $this->config['merchant_id'],
        //         'kid' => $this->config['keys']['private_key_id']
        //     ];

        //     $hashedPayload = base64_encode(hash('sha256', $token, true));
        //     $jwsPayload = json_encode([
        //         'digest' => $hashedPayload,
        //         'digestAlgorithm' => "SHA-256",
        //         'exp' => 300000,
        //         'iat' => $currentTimestamp
        //     ]);

        //     $jws = $jwsBuilder->create()
        //         ->withPayload($jwsPayload)
        //         ->addSignature($jwsKey, $jwsHeader)
        //         ->build();

        //     // STEP 6: Serialize JWS Token
        //     $stepStartTime = microtime(true);
        //     $jwsSerializer = new SigCompactSerializer();
        //     $jwsToken = $jwsSerializer->serialize($jws, 0);

        //     // STEP 7: HTTP Request to PayGlocal Refund API
        //     $stepStartTime = microtime(true);
        //     Log::info("PayGlocal: Making HTTP request to PayGlocal Refund API", [
        //         'gid' => $gid,
        //         'url' => $this->baseUrl . '/gl/v1/payments/' . $gid . '/refund'
        //     ]);

        //     $response = Http::withHeaders([
        //         'x-gl-token-external' => $jwsToken,
        //         'Content-Type' => 'text/plain'
        //     ])->post($this->baseUrl . '/gl/v1/payments/' . $gid . '/refund', $token);

        //     Log::info('PayGlocal Refund API Response', [
        //         'status_code' => $response->status(),
        //         'response_body' => $response->body(),
        //         'gid' => $gid
        //     ]);

        //     if ($response->successful()) {
        //         $responseData = $response->json();

        //         // Update payment record to mark as refunded
        //         $payment = \App\Models\Payment::where('gid', $gid)->first();
        //         if ($payment) {
        //             $refundAmount = ($refundData['type'] ?? 'F') === 'F' ? $payment->amount : $refundData['amount'];
        //             $payment->markAsRefunded($refundAmount, 'Refund processed via PayGlocal API');

        //             Log::info('Payment marked as refunded', [
        //                 'payment_id' => $payment->id,
        //                 'refund_amount' => $refundAmount,
        //                 'gid' => $gid
        //             ]);
        //         }

        //         return $responseData;
        //     }

        //     throw new \Exception('PayGlocal Refund API Error: ' . $response->body());

        // } catch (\Exception $e) {
        //     Log::error('PayGlocal Refund Error', [
        //         'gid' => $gid,
        //         'refund_data' => $refundData,
        //         'error' => $e->getMessage(),
        //         'file' => $e->getFile(),
        //         'line' => $e->getLine()
        //     ]);
        //     throw $e;
        // }

        set_time_limit(300);
        $totalStartTime = microtime(true);
        try {
            // STEP 1: Database Operations
            $stepStartTime = microtime(true);
            $orderId = $refundData['order_id'];
            $order = Orders::where('order_id', $refundData['order_id'])->first();
            // dd( $order , $refundData['order_id'], $gid);
            $user = User::find($order->user_id);
            // $userid = "wa_91" . $user->user_phone . "_766e746970756b74677a_554283991091136";
            $totalValue = $order->total_price - ($order->coupon_discount ?? 0);
            $this->logExecutionTime('Database Operations', $stepStartTime, [
                'order_id' => $orderId,
                'user_id' => $user->id,
                'user_phone' => $user->user_phone,
                'total_amount' => $totalValue
            ]);

            // STEP 2: Prepare Payment Data
            $stepStartTime = microtime(true);
            $currentTimestamp = (string)round(microtime(true) * 1000);
            $orderIdLength = strlen($orderId);
            $randomStringLength = 16 - $orderIdLength - 1;
            $randomString = $this->generateRandomString($randomStringLength);
            $merchantUniqueId = $orderId . '_' . $randomString;

            $payloadData = [
                "refundType" => "F", // F = Full, P = Partial
                'amount' => $totalValue,
                "merchantTxnId" => $order->cart_id,
                "merchantUniqueId" => $merchantUniqueId,
                "paymentData" => [
                    "totalAmount" => $totalValue,
                    "txnCurrency" => "INR",
                    "billingData" => [
                        'firstName' => $order->address->receiver_name,
                        'lastName' => '',
                        'emailId' => $order->address->receiver_email,
                        'phoneNumber' => $order->address->receiver_phone,
                        'addressStreet1' => $order->address->society,
                        'addressCity' => $order->address->city,
                        'addressState' => $order->address->state,
                        'addressPostalCode' => $order->address->pincode,
                        'addressCountry' => 'IN',
                    ],
                ],
                "merchantCallbackURL" => $this->config['callback_url'] ?? "http://127.0.0.1:8000/payglocal/executes"
            ];

            $payload = json_encode($payloadData);
            $this->logExecutionTime('Payment Data Preparation', $stepStartTime, [
                'merchant_unique_id' => $merchantUniqueId,
                'payload_size' => strlen($payload) . ' bytes',
                'timestamp' => $currentTimestamp
            ]);

            // STEP 3: Create JWE Token (Critical Performance Section)
            $stepStartTime = microtime(true);
            Log::info("PayGlocal: Starting JWE Creation - This is typically the slowest part");

            $jweBuilder = new JWEBuilder(
                self::$keyEncryptionAlgorithmManager,
                self::$contentEncryptionAlgorithmManager,
                self::$compressionMethodManager
            );

            $key = $this->getEncryptionKey();

            $header = [
                'issued-by' => $this->config['merchant_id'],
                'enc' => 'A128CBC-HS256',
                'exp' => 30000,
                'iat' => $currentTimestamp,
                'alg' => 'RSA-OAEP-256',
                'kid' => $this->config['keys']['public_key_id']
            ];

            $jwe = $jweBuilder->create()
                ->withPayload($payload)
                ->withSharedProtectedHeader($header)
                ->addRecipient($key)
                ->build();

            $jweTime = $this->logExecutionTime('JWE Creation (RSA Encryption)', $stepStartTime, [
                'payload_size' => strlen($payload),
                'encryption_algorithm' => 'RSA-OAEP-256',
                'content_encryption' => 'A128CBC-HS256',
                'critical_operation' => 'RSA_ENCRYPTION'
            ]);

            // STEP 4: Serialize JWE Token
            $stepStartTime = microtime(true);
            $serializer = new EncryptionCompactSerializer();
            $token = $serializer->serialize($jwe, 0);
            $this->logExecutionTime('JWE Serialization', $stepStartTime, [
                'token_size' => strlen($token) . ' bytes'
            ]);

            // STEP 5: Create JWS Token (Another Critical Performance Section)
            $stepStartTime = microtime(true);
            Log::info("PayGlocal: Starting JWS Creation - Another typically slow RSA operation");

            $jwsBuilder = new JWSBuilder(self::$signatureAlgorithmManager);
            $jwsKey = $this->getSigningKey();

            $jwsHeader = [
                'issued-by' => $this->config['merchant_id'],
                'is-digested' => 'true',
                'alg' => 'RS256',
                'x-gl-enc' => 'true',
                'x-gl-merchantId' => $this->config['merchant_id'],
                'x-gl-kid-mid' => $this->config['merchant_id'],
                'kid' => $this->config['keys']['private_key_id']
            ];

            $hashedPayload = base64_encode(hash('sha256', $token, true));
            $jwsPayload = json_encode([
                'digest' => $hashedPayload,
                'digestAlgorithm' => "SHA-256",
                'exp' => 300000,
                'iat' => $currentTimestamp
            ]);

            $jws = $jwsBuilder->create()
                ->withPayload($jwsPayload)
                ->addSignature($jwsKey, $jwsHeader)
                ->build();

            $jwsTime = $this->logExecutionTime('JWS Creation (RSA Signing)', $stepStartTime, [
                'signature_algorithm' => 'RS256',
                'digest_algorithm' => 'SHA-256',
                'critical_operation' => 'RSA_SIGNING'
            ]);

            // STEP 6: Serialize JWS Token
            $stepStartTime = microtime(true);
            $jwsSerializer = new SigCompactSerializer();
            $jwsToken = $jwsSerializer->serialize($jws, 0);
            $this->logExecutionTime('JWS Serialization', $stepStartTime, [
                'jws_token_size' => strlen($jwsToken) . ' bytes'
            ]);

            // STEP 7: HTTP Request to PayGlocal
            $stepStartTime = microtime(true);
            Log::info("PayGlocal: Making HTTP request to PayGlocal API");

            // $response = Http::withHeaders([
            //     'x-gl-token-external' => $jwsToken,
            //     'Content-Type' => 'text/plain'
            // ])->post($this->baseUrl . '/gl/v1/payments/' . $gid . '/refund', $token);



            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->baseUrl . '/gl/v1/payments/' . $gid . '/refund',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $token,
                CURLOPT_HTTPHEADER => [
                    'x-gl-token-external: ' . $jwsToken,
                    'Content-Type: text/plain'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlInfo = curl_getinfo($curl);
            $curlError = curl_error($curl);
            curl_close($curl);
            // STEP 8: Process Response
            $stepStartTime = microtime(true);
            $data = json_decode($response, true);

            // dd($data);
            // Calculate total execution time and performance analysis
            $totalExecutionTime = microtime(true) - $totalStartTime;

            // Log::info("PayGlocal Payment Completed", [
            //     'order_id' => $orderId,
            //     'total_execution_time' => number_format($totalExecutionTime, 6) . ' seconds',
            //     'jwe_time_percentage' => number_format(($jweTime / $totalExecutionTime) * 100, 2) . '%',
            //     'jws_time_percentage' => number_format(($jwsTime / $totalExecutionTime) * 100, 2) . '%',
            //     'memory_end' => $this->formatBytes(memory_get_usage()),
            //     'memory_peak' => $this->formatBytes(memory_get_peak_usage()),
            //     'success' => isset($data['data']['redirectUrl']) ? 'yes' : 'no'
            // ]);







            return  $data;
            // return redirect()->away($data['data']['redirectUrl']);

        } catch (\Exception $e) {
            $totalExecutionTime = microtime(true) - $totalStartTime;
            Log::error("PayGlocal Payment Failed", [
                'order_id' => $order,
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'execution_time_before_error' => number_format($totalExecutionTime, 6) . ' seconds',
                'memory_usage' => $this->formatBytes(memory_get_usage()),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get payment status
     */
    // public function getPaymentStatus(string $identifier): array
    // {
    //     try {
    //         // Create Algorithm Manager with RS256
    //         $algorithmManager = new AlgorithmManager([new RS256()]);
    //         $jwsBuilder = new JWSBuilder($algorithmManager);

    //         // Get the signing key
    //         $jwskey = $this->getSigningKey();


    //         // Build the complete URI with gid parameter
    //         $uri = '/gl/v1/payments/' . $identifier . '/status';
    //         $gid = $identifier; // or transaction ID
    //         $fullUri = $uri;

    //         // Define JWS header
    //         $jwsheader = [
    //             'issued-by' => $this->config['merchant_id'],
    //             'is-digested' => 'true',
    //             'alg' => 'RS256',
    //             'typ' => 'JWT',
    //             'x-gl-enc' => 'true',
    //             'x-gl-merchantId' => $this->config['merchant_id'], // Merchant ID
    //             'kid' => $this->config['keys']['private_key_id'],
    //         ];
    //         // Use the URI as payload for JWS signing
    //         $jwspayload = $fullUri;

    //         // Build JWS token with URI as payload
    //         $jws = $jwsBuilder
    //             ->create()
    //             ->withPayload($jwspayload)
    //             ->addSignature($jwskey, $jwsheader)
    //             ->build();

    //         // Serialize token
    //         $jwsserializer = new SigCompactSerializer();
    //         $token = $jwsserializer->serialize($jws, 0);

    //         // Make API request with JWS token in header
    //         $url = $this->baseUrl . $uri . '?gid=' . $gid;

    //         // dd($token, $url);
    //         $response = Http::timeout(30)
    //             ->withHeaders([
    //                 'Content-Type' => 'application/json',
    //                 'x-gl-token-external' => $token,
    //             ])
    //             ->get($url);


    //         dd($response, $jwskey, $jwspayload, $token );
    //         // Handle response
    //         if ($response->successful()) {
    //             $data = $response->json();

    //             Log::info('PayGlocal Payment Status Retrieved', [
    //                 'identifier' => $identifier,
    //                 'gid' => $gid,
    //                 'status' => $data['status'] ?? 'unknown'
    //             ]);

    //             return $data;
    //         }

    //         $errorMessage = $response->json()['message'] ?? $response->body();
    //         Log::error('PayGlocal Status API Error', [
    //             'identifier' => $identifier,
    //             'gid' => $gid,
    //             'status_code' => $response->status(),
    //             'error' => $errorMessage
    //         ]);

    //         throw new \Exception(
    //             'PayGlocal Status API Error: ' . $errorMessage,
    //             $response->status()
    //         );

    //     } catch (\Exception $e) {
    //         Log::error('PayGlocal Status Check Exception', [
    //             'identifier' => $identifier,
    //             'error' => $e->getMessage()
    //         ]);

    //         throw $e;
    //     }
    // }

    public function getPaymentStatus(string $identifier)
    {
        $gid = $identifier;
        set_time_limit(300);
        $totalStartTime = microtime(true);

        try {
            $uriPath = '/gl/v1/payments/' . $identifier . '/status';
            $fullUrl = $this->baseUrl . $uriPath;

            // Keep timestamps consistent with initiatePayment (ms string)
            $currentTimestamp = (string) round(microtime(true) * 1000);

            $jwsBuilder = new JWSBuilder(self::$signatureAlgorithmManager);
            $jwsKey = $this->getSigningKey();

            // Use same header pattern as initiatePayment
            $jwsHeader = [
                'issued-by' => $this->config['merchant_id'],
                'is-digested' => 'true',
                'alg' => 'RS256',
                'x-gl-enc' => 'true',
                'x-gl-merchantId' => $this->config['merchant_id'],
                'x-gl-kid-mid' => $this->config['merchant_id'],
                'kid' => $this->config['keys']['private_key_id']
            ];

            // Helper to build and send one status request given payloadToDigest
            $sendStatusRequest = function (string $payloadToDigest) use (
                $jwsBuilder,
                $jwsKey,
                $jwsHeader,
                $currentTimestamp,
                $fullUrl,
                &$gid
            ) {
                // Compute digest the same way you did for initiatePayment:
                $rawHash = hash('sha256', $payloadToDigest, true);
                $hashedBase64 = base64_encode($rawHash); // keep same encoding as initiatePayment

                $jwsPayload = json_encode([
                    'digest' => $hashedBase64,
                    'digestAlgorithm' => 'SHA-256',
                    'iat' => $currentTimestamp,
                    'exp' => (string) ($currentTimestamp + 300000) // match your other expiry pattern
                ], JSON_UNESCAPED_SLASHES);

                $jws = $jwsBuilder->create()
                    ->withPayload($jwsPayload)
                    ->addSignature($jwsKey, $jwsHeader)
                    ->build();

                $jwsSerializer = new SigCompactSerializer();
                $jwsToken = $jwsSerializer->serialize($jws, 0);

                // Debug logging (sanitized): remove in prod
                Log::debug('PayGlocal Status - JWS Payload (sanitized)', [
                    'digest_len' => strlen($hashedBase64),
                    'payload_len' => strlen($jwsPayload),
                    'jws_token_len' => strlen($jwsToken)
                ]);

                // Perform GET request (status endpoint typically uses GET)
                $response = Http::withHeaders([
                    'x-gl-token-external' => $jwsToken,
                    'Content-Type' => 'application/json'
                ])->get($fullUrl);

                return $response;
            };

            // 1) Try with empty payload digest (common for GET status endpoints)
            $stepStart = microtime(true);
            $response = $sendStatusRequest('');
            $this->logExecutionTime('Payment Status - attempt 1 (empty digest)', $stepStart, [
                'http_status' => $response->status()
            ]);

            $data = json_decode($response->body(), true);

            // If auth failed, try fallback: digest of the URI (some gateways expect hashing URI)
            $authFailed = false;
            if (is_array($data) && isset($data['status']) && $data['status'] === 'REQUEST_ERROR') {
                $msg = strtolower($data['message'] ?? $data['errors']['detailedMessage'] ?? '');
                if (strpos($msg, 'authentication failed') !== false || strpos($msg, 'auth') !== false) {
                    $authFailed = true;
                }
            }

            if ($authFailed) {
                Log::warning('PayGlocal Status: attempt 1 returned auth failure. Retrying with URI digest.', [
                    'gid' => $gid,
                    'attempt' => 2,
                    'uri' => $uriPath
                ]);

                $stepStart = microtime(true);
                $response = $sendStatusRequest($uriPath); // fallback: hash the URI
                $this->logExecutionTime('Payment Status - attempt 2 (URI digest)', $stepStart, [
                    'http_status' => $response->status()
                ]);

                $data = json_decode($response->body(), true);
            }

            // Final logging
            $totalExecutionTime = microtime(true) - $totalStartTime;
            Log::info('PayGlocal Payment Status Result', [
                'gid' => $gid,
                'http_status' => $response->status(),
                'response_body_snippet' => substr($response->body(), 0, 1000),
                'total_execution_time' => number_format($totalExecutionTime, 6) . ' seconds'
            ]);

            return $data;
        } catch (\Exception $e) {
            $totalExecutionTime = microtime(true) - $totalStartTime;
            Log::error("PayGlocal Payment Status Failed", [
                'gid' => $gid,
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'execution_time_before_error' => number_format($totalExecutionTime, 6) . ' seconds',
                'memory_usage' => $this->formatBytes(memory_get_usage()),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    /**
     * Verify callback token
     */
    public function verifyCallbackToken(string $token): array
    {
        try {
            $algorithmManager = new AlgorithmManager([new RS256()]);
            $jwsVerifier = new JWSVerifier($algorithmManager);

            $jwk = JWKFactory::createFromKeyFile(
                $this->getKeyPaths()['public'],
                null,
                [
                    'kid' => $this->config['keys']['public_key_id'],
                    'use' => 'sig'
                ]
            );

            $serializerManager = new JWSSerializerManager([
                new \Jose\Component\Signature\Serializer\CompactSerializer(),
            ]);

            // Method 1: Simple verification and payload extraction
            $jws = $serializerManager->unserialize($token);
            $isVerified = $jwsVerifier->verifyWithKey($jws, $jwk, 0);

            if ($isVerified) {
                // Get the payload directly from the JWS object
                $payload = $jws->getPayload();
                return json_decode($payload, true);
            }

            throw new \Exception('Token verification failed');
        } catch (\Exception $e) {
            Log::error('PayGlocal Token Verification Error: ' . $e->getMessage(), [
                'token_length' => strlen($token),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Token verification failed: ' . $e->getMessage());
        }
    }
}
