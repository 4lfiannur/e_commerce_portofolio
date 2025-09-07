<?php

return [
    'merchant_id'  => env('MIDTRANS_MERCHANT_ID'),
    'client_key'   => env('MIDTRANS_CLIENT_KEY'),
    'server_key'   => env('MIDTRANS_SERVER_KEY'),
    'is_production'=> env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => true,
    'enable_3ds'   => true,

    'payment_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js'
];
