<?php

return [
    // Login dengan Google (Laravel Socialite)
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', rtrim(env('APP_URL', 'http://localhost:8000'), '/').'/auth/google/callback'),
    ],

    // Pembayaran Midtrans Snap
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
        // false hanya untuk lokal bila PHP di Windows belum punya sertifikat CA (cURL error 60)
        'verify_ssl' => (bool) env('MIDTRANS_VERIFY_SSL', true),
    ],
];
