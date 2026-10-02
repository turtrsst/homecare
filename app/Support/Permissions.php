<?php

namespace App\Support;

use App\Enums\UserRole;

/**
 * Peta terpusat role → kemampuan (gate).
 *
 * Semua pemeriksaan otorisasi kasar memakai Gate/policy — tidak ada
 * `if ($user->role == 'admin')` yang tersebar di kode. Tambah role atau
 * kemampuan baru cukup mengubah peta ini.
 */
final class Permissions
{
    /**
     * @return array<string, list<UserRole>>
     */
    public static function map(): array
    {
        return [
            // Akses area operasional rumah sakit
            'operational.access' => [UserRole::Admin, UserRole::Coordinator, UserRole::Manager],

            // Verifikasi & skrining pengajuan
            'requests.verify' => [UserRole::Admin, UserRole::Coordinator],

            // Penjadwalan, assignment petugas, konfirmasi biaya
            'requests.schedule' => [UserRole::Admin, UserRole::Coordinator],

            // Pencatatan pembayaran
            'payments.manage' => [UserRole::Admin, UserRole::Coordinator],

            // Master layanan & tarif
            'services.manage' => [UserRole::Admin],

            // Data tenaga kesehatan & assignment
            'staff.manage' => [UserRole::Admin, UserRole::Coordinator],

            // Melihat semua data pasien (lintas akun)
            'patients.viewAll' => [UserRole::Admin, UserRole::Coordinator, UserRole::Manager, UserRole::MedicalStaff],

            // Laporan & monitoring
            'reports.view' => [UserRole::Admin, UserRole::Coordinator, UserRole::Manager],

            // Audit trail
            'audit.view' => [UserRole::Admin, UserRole::Manager],

            // Manajemen pengguna
            'users.manage' => [UserRole::Admin],
        ];
    }

    public static function roleCan(UserRole $role, string $ability): bool
    {
        return in_array($role, self::map()[$ability] ?? [], true);
    }
}
