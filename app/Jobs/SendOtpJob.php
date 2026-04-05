<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;

    // Retry config
    public int $tries = 3;          // total attempts
    public int $backoff = 30;       // seconds between retries

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        try {

            if ($this->data['is_email']) {
                $email = $this->data['email'];
                $name  = $this->data['name'] ?? 'User';

                try {
                    Mail::send(
                        'frontend.mail.email-otp',
                        [
                            'name' => $name,
                            'otp' => $this->data['otp'],
                            'expiryMinutes' => 10,
                        ],
                        function ($message) use ($email, $name) {
                            $message->to($email, $name)
                                ->subject("Login OTP - Amma's Spices");
                        }
                    );
                } catch (\Throwable $e) {
                    Log::error('Email OTP sending failed', [
                        'email' => $email,
                        'error' => $e->getMessage(),
                    ]);

                    // IMPORTANT: rethrow so queue retries
                    throw $e;
                }
            }

            // // Call SMS provider API
            // $smsParams = [
            //     'user' => config('services.sms.user', 'bodhibliss'),
            //     'password' => config('services.sms.password'),
            //     'senderid' => config('services.sms.sender_id', 'BBSOAP'),
            //     'channel' => 'Trans',
            //     'DCS' => 0,
            //     'flashsms' => 0,
            //     'route ' => 42,
            //     'number' => '91' . $this->data['mobile_no'], // Changed from 'mobile' to 'number', removed '+'
            //     'text' => 'Use OTP ' . $this->data['otp'] . " to log in to your Amma's Spices account. -Amma's Spices", // Changed from 'message' to 'text'
            //     'Peid' => config('services.sms.peid', '1701176414187427388'), // Changed from 'entityid' to 'Peid'
            //     'DLTTemplateId' => '1707176847613919240' // Changed from 'tempid' to 'DLTTemplateId'
            // ];




            // // Use GET instead of POST
            // $response = Http::get('http://www.admagister.net/api/mt/SendSMS', $smsParams);
            // // dd($smsParams);


            // // Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6Inpha2hfYm90IiwiZW1haWwiOiJ6YWtoQG1haWxpbmF0b3IuY29tIiwidGltZXN0YW1wIjoiMjAyNS0xMC0zMVQwNjozNTozMS4zNTJaIiwiY2hhbm5lbCI6IndoYXRzYXBwIiwiaWF0IjoxNzYxODkyNTMxfQ.gmwdMS60eECJ9Mdx3VUq9KvMMnBZXQET2U6PVpfDHeI')
            // //     ->post('https://api.helloyubo.com/v3/whatsapp/notification', [
            // //         "clientId" => "zakh_bot",
            // //         "channel" => "whatsapp",
            // //         "send_to" => '91' . $this->mobile,
            // //         "templateName" => "log_in_otp",
            // //         "parameters" => ["$this->otp"],
            // //         "msg_type" => "TEXT",
            // //         "header" => "",
            // //         "footer" => "",
            // //         "buttonUrlParam" => $this->otp,
            // //         "button" => "true",
            // //         "media_url" => "",
            // //         "lang" => "en_US",
            // //         "msg" => null,
            // //         "userName" => null,
            // //         "parametersArrayObj" => null,
            // //         "headerParam" => null
            // //     ]);



            // Log::info("OTP sent", [
            //     'phone' => $this->data['mobile_no'],
            //     'otp'   => $this->data['otp'],
            //     'data' => $this->data
            // ]);

            // Log::info("OTP Response", [
            //     'response' => $response->json(),
            //     'data' => $this->data,
            //     'params' => $smsParams,
            //     'config' => config('services.sms')
            // ]);
        } catch (\Throwable $e) {
            Log::error("OTP sending failed", [
                'phone' => $this->data['mobile_no'],
                'error' => $e->getMessage(),
            ]);

            // rethrow to trigger retry
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical("OTP job permanently failed", [
            'phone' => $this->data['mobile_no'],
            'error' => $exception->getMessage(),
        ]);
    }
}
