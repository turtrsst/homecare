<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seed data demo aplikasi Soeradji Care.
 *
 * Akun demo (password: "password"):
 *   admin@homecare.rs        — Administrator
 *   koordinator@homecare.rs  — Koordinator Homecare
 *   manajer@homecare.rs      — Manajer (laporan & audit)
 *   dr.andini@homecare.rs    — Dokter (area tugas)
 *   ns.siti@homecare.rs      — Perawat wound care (area tugas)
 *   ns.bagus@homecare.rs     — Perawat (area tugas)
 *   ft.rina@homecare.rs      — Fisioterapis (area tugas)
 *   budi@example.com         — Pasien (keluarga)
 *   sari@example.com         — Pasien (keluarga)
 *
 * Jalankan: php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HomecareServiceSeeder::class,
            DemoUserSeeder::class,
            DemoRequestSeeder::class,
        ]);
    }
}
