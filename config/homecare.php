<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identitas rumah sakit dan branding publik
    |--------------------------------------------------------------------------
    */
    'brand_name' => 'Soeradji Care',
    'hospital_name' => env('HOMECARE_HOSPITAL_NAME', 'RSUP Dr. Soeradji Tirtonegoro Klaten'),
    'tagline' => 'Kesehatan di Rumah, Lebih Mudah.',
    'logo_path' => '/images/soeradji-care-logo.svg',
    'emergency_number' => env('HOMECARE_EMERGENCY_NUMBER', '119'),
    'contact' => [
        'phone' => env('HOMECARE_CONTACT_PHONE', '(0272) 321020'),
        'whatsapp' => env('HOMECARE_CONTACT_WA', '(0272) 321020'),
        'email' => env('HOMECARE_CONTACT_EMAIL', 'rsupsoeradji_klaten@yahoo.com'),
        'address' => env('HOMECARE_CONTACT_ADDRESS', 'Jl. KRT. dr. Soeradji Tirtonegoro No. 1, Tegalyoso, Klaten Selatan, Klaten, Jawa Tengah 57424'),
    ],
    'website' => env('HOMECARE_WEBSITE', 'https://rsupsoeradji.id'),
    'hours' => 'Senin–Jumat, 08.00–17.00 WIB (IGD 24 jam)',
    'min_lead_days' => (int) env('HOMECARE_MIN_LEAD_DAYS', 1),
    'max_advance_days' => (int) env('HOMECARE_MAX_ADVANCE_DAYS', 30),
    'time_windows' => [
        'morning' => ['label' => 'Pagi (08.00 – 12.00)', 'start' => '08:00', 'end' => '12:00'],
        'midday' => ['label' => 'Siang (12.00 – 16.00)', 'start' => '12:00', 'end' => '16:00'],
        'afternoon' => ['label' => 'Sore (16.00 – 20.00)', 'start' => '16:00', 'end' => '20:00'],
    ],
    'uploads' => [
        'disk' => 'private',
        'max_size_kb' => 5120,
        'mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
        'collections' => [
            'identity' => 'Identitas (KTP/KK/Kartu Berobat)',
            'referral' => 'Surat Rujukan',
            'medical_result' => 'Hasil Pemeriksaan',
            'condition_photo' => 'Foto Kondisi',
            'service_documentation' => 'Dokumentasi Pelayanan',
            'other' => 'Dokumen Lain',
        ],
    ],
];
