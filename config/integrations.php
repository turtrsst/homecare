<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Provider Data Pasien
    |--------------------------------------------------------------------------
    | "local"  => LocalPatientProvider (database aplikasi ini, source of truth).
    | Masa depan: "simrs" (SimrsPatientProvider), "satusehat"
    | (SatusehatPatientProvider). Ganti driver tanpa mengubah business logic.
    */
    'patient_provider' => env('INTEGRATION_PATIENT_PROVIDER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Provider Pembayaran
    |--------------------------------------------------------------------------
    | "manual" => pencatatan pembayaran oleh petugas (default standalone).
    | Masa depan: driver gateway (midtrans/xendit/...) via PaymentGatewayProvider.
    */
    'payment_provider' => env('INTEGRATION_PAYMENT_PROVIDER', 'manual'),

    /*
    |--------------------------------------------------------------------------
    | Pengirim Pesan (WhatsApp/SMS)
    |--------------------------------------------------------------------------
    | "log" => hanya mencatat (dev). Masa depan: "whatsapp_gateway".
    */
    'message_sender' => env('INTEGRATION_MESSAGE_SENDER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Peta / Geocoding
    |--------------------------------------------------------------------------
    | "null" => tidak melakukan geocoding. Masa depan: "google".
    */
    'maps_provider' => env('INTEGRATION_MAPS_PROVIDER', 'null'),

    /*
    | Konfigurasi per-integrasi juga tersimpan di tabel `api_integrations`
    | (dikelola admin) — nilai di sini adalah default/fallback.
    */
];
