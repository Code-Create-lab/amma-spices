<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessIthinkTrackingUpdate;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\Str;
use Symfony\Component\HttpFoundation\Response;

class IthickLogisticsController extends Controller
{
    public function receive(Request $request)
    {
        // 1) Read raw body and headers
        $raw = $request->getContent();
        $signature = $request->header('X-ITHINK-SIGNATURE'); // example header name - confirm with them

        // 2) Verify signature (HMAC SHA256)
        if (! $this->verifySignature($raw, $signature)) {
            Log::warning('Ithink webhook signature mismatch', ['headers' => $request->headers->all()]);
            return response()->json(['message' => 'Invalid signature'], Response::HTTP_UNAUTHORIZED);
        }

        // 3) Validate JSON quickly
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        // minimal required fields check
        if (empty($data['awb_number'])) {
            return response()->json(['message' => 'Missing awb_number'], Response::HTTP_BAD_REQUEST);
        }

        // 4) Save raw payload to logs (helps debugging & idempotency)
        try {
            WebhookLog::create([
                'awb_number' => $data['awb_number'] ?? 'unknown',
                'logistics_name' => $data['logistics_name'] ?? null,
                'payload' => $data,
                'status_code' => $data['current_tracking_status'] ?? null,
                'signature' => $signature,
                'processed' => false,
            ]);
        } catch (\Exception $e) {
            // If duplicate unique constraint triggers, continue. But generally log it.
            Log::error('WebhookLog create failed: ' . $e->getMessage());
            // still continue
        }

        // dd($data);   
        // 5) Dispatch to a queue job for processing (fast response to sender)
              ProcessIthinkTrackingUpdate::dispatch($data, $signature);

        // 6) Immediately return 200 OK (acknowledge)
        return response()->json(['status' => 'ok'], Response::HTTP_OK);
    }

    private function verifySignature(string $payload, ?string $signature): bool
    {
        $secret = env('ITHINK_WEBHOOK_SECRET');

        if (empty($secret) || empty($signature)) {
            return false;
        }

        // Accept raw token for dev/testing only
        if (app()->environment('local') && hash_equals($signature, $secret)) {
            return true;
        }

        if (Str::startsWith($signature, 'sha256=')) {
            $sig = substr($signature, 7);
            $computed = hash_hmac('sha256', $payload, $secret);
            return hash_equals($computed, $sig);
        }

        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }
}
