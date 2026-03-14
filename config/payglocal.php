<?php

return [
    'environment' => env('PAYGLOCAL_ENVIRONMENT', 'uat'),
    'merchant_id' => env('PAYGLOCAL_MERCHANT_ID'),
    'base_urls' => [
        'uat' => env('PAYGLOCAL_BASE_URL_UAT', 'https://api.uat.payglocal.in'),
        'production' => env('PAYGLOCAL_BASE_URL_PROD', 'https://api.prod.payglocal.in'),
    ],
    'keys' => [
        'private_key_path' => env('PAYGLOCAL_PRIVATE_KEY_PATH'),
        'public_key_path' => env('PAYGLOCAL_PUBLIC_KEY_PATH'),
        'private_key_id' => env('PAYGLOCAL_PRIVATE_KEY_ID'),
        'public_key_id' => env('PAYGLOCAL_PUBLIC_KEY_ID'),
    ],
    'callback_url' => env('PAYGLOCAL_CALLBACK_URL'),
];