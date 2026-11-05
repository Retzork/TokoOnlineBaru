<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans (Snap) Configuration
    |--------------------------------------------------------------------------
    |
    | Kredensial diambil dari dashboard Midtrans. Gunakan kunci Sandbox saat
    | pengembangan, lalu ganti dengan kunci Production ketika aplikasi live.
    | https://dashboard.sandbox.midtrans.com/settings/config_info
    |
    */

    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),

    'is_sanitized' => (bool) env('MIDTRANS_IS_SANITIZED', true),

    'is_3ds' => (bool) env('MIDTRANS_IS_3DS', true),

    /*
    | Snap.js dimuat di halaman pembayaran. URL Sandbox dan Production berbeda.
    */
    'snap_url' => env('MIDTRANS_SNAP_URL') ?: (
        env('MIDTRANS_IS_PRODUCTION', false)
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js'
    ),

];
