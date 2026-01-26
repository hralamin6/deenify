<?php

return [
    // set this to true to enable sandbox mode
    'sandbox' => env('AAMARPAY_SANDBOX_MODE', true),

    // AamarPay credentials [Change these with your details]
    'store_id' => env('AAMARPAY_STORE_ID', 'aamarpaytest'),
    'signature_key' => env('AAMARPAY_SIGNATURE_KEY', 'dbb74894e82415a2f7ff0ec3a97e4183'),

    // Redirect URLs (routes or urls)
    'redirect_url' => [
        'success' => [
            'url' => '', // Will be set dynamically in the component
        ],
        'cancel' => [
            'url' => '', // Will be set dynamically in the component
        ],
    ],
];
