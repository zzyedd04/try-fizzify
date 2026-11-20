<?php

return [
    // Nomor WhatsApp tujuan form Contact (format internasional tanpa + atau 0 di depan)
    'whatsapp' => env('FIZZIFY_WHATSAPP', '6286582391224'),
    'whatsapp_display' => env('FIZZIFY_WHATSAPP_DISPLAY', '(+62) 865 8239 1224'),
    'email' => env('FIZZIFY_EMAIL', 'fizzify@gmail.com'),

    // Email Google yang boleh masuk sebagai admin lewat tombol "Sign in with Google" di halaman admin
    'admin_emails' => array_filter(array_map('trim', explode(',', env('ADMIN_EMAILS', '')))),

    // Nomor rekening yang tampil di kartu Active Balance dashboard
    'account_number' => env('FIZZIFY_ACCOUNT_NUMBER', '70620043067'),

    // Persentase reward dari omzet bulan berjalan
    'reward_rate' => 0.05,
];
