<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'twilio' => [
        'twilio_account_id' => env('TWILIO_ACCOUNT_ID'),
        'twilio_account_auth' => env('TWILIO_ACCOUNT_AUTH'),
        'twilio_service_id' => ENV('TWILIO_SERVICE_ID')
    ],
    'ithinklogistics' => [
        'base_url' => env('ITHINK_BASE_URL', 'https://pre-alpha.ithinklogistics.com/api_v3'),
        'access_token' => env('ITHINK_ACCESS_TOKEN'),
        'secret_key' => env('ITHINK_SECRET_KEY'),
        'default_pickup_address_id' => env('ITHINK_PICKUP_ADDRESS_ID', '24'),
        'default_return_address_id' => env('ITHINK_RETURN_ADDRESS_ID', '24'),
        'logistics_name'    => env('ITHINK_LOGISTICS_NAME', 'Delhivery'),
    ],
   'razorpay' => [
        'key'    => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],
    'shiprocket' => [
        'email' => env('SHIPROCKET_EMAIL'),
        'password' => env('SHIPROCKET_PASSWORD'),
        'webhook_secret' => env('SHIPROCKET_SECRET')
    ],
    'whatsapp' => [
        'api_token' => env('WHATSAPP_API_TOKEN'),
    ],
    'sms' => [
        'user' => env('SMS_USER', 'bodhibliss'),
        'password' => env('SMS_PASSWORD'),
        'sender_id' => env('SMS_SENDER_ID', 'BBSOAP'),
        'peid' => env('SMS_PEID', '1701176414187427388'),
        'default_template_id' => env('SMS_DEFAULT_TEMPLATE_ID', '1707176361958332349'),
    ],


];
