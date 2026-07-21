<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services - SI-IPULBUAH
    |--------------------------------------------------------------------------
    | PENTING: gabungkan array ini ke dalam config/services.php bawaan Laravel
    | Anda (jangan menimpa penuh), karena Laravel juga punya entri postmark,
    | resend, ses, slack, dll. di file yang sama.
    */

    'wablas' => [
        'base_url' => env('WABLAS_BASE_URL', 'https://console.wablas.com'),
        'api_key' => env('WABLAS_API_KEY'),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],
];
