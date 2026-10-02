<?php

namespace App\Integrations\Contracts;

/**
 * Sumber data pasien. Sekarang diimplementasikan oleh LocalPatientProvider
 * (database aplikasi). Ketika SIMRS/SATUSEHAT tersedia, buat implementasi baru
 * dan ganti binding — business logic tidak berubah.
 */
interface PatientDataProvider
{
    /**
     * Cari pasien berdasarkan identifier eksternal (NIK / nomor RM).
     *
     * @return array<string, mixed>|null Data ternormalisasi:
     *   name, nik, gender, birth_date, phone, address, external_id, source
     */
    public function findByIdentifier(string $identifier): ?array;

    /** Nama provider untuk ditampilkan/di-log. */
    public function name(): string;
}
