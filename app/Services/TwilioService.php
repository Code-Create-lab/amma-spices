<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Twilio\Rest\Client;

class TwilioService
{
    public static function GenerateOtp($phone_number)
    {
        try {
            $twilio = new Client(config('services.twilio.twilio_account_id'), config('services.twilio.twilio_account_auth'));
            $verification = $twilio->verify->v2->services(config('services.twilio.twilio_service_id'))
                ->verifications
                ->create($phone_number, "sms");
            return response()->json($verification, 200);
        } catch (\Twilio\Exceptions\TwilioException $e) {
            $response = ['error' => $e->getMessage()];
            return response()->json($response, 404);
        }
    }

    public function verifyOtp($otp){
        
        $twilio = new Client(config('services.twilio.twilio_account_id'), config('services.twilio.twilio_account_auth'));
        try {
            $checkCode = $twilio
                ->verify
                ->v2
                ->services(config('services.twilio.twilio_service_id'))
                ->verificationChecks
                ->create([
                    "to" => '+91' . auth()->user()->user_phone,
                    "code" => $otp,
                ]);

            if ($checkCode->valid == true) {
                session()->put('2fa_completed_' . Auth::id(), true);
                return redirect()->route('customer.dashboard.index');
            } else {
                return redirect()->back()->with('error', 'Invalid Otp');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid Otp Entered');
        }
    }
}
