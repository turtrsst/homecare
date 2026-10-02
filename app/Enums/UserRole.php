<?php

namespace App\Enums;

enum UserRole: string
{
    case Patient = 'patient';
    case Admin = 'admin';
    case Coordinator = 'coordinator';
    case MedicalStaff = 'medical_staff';
    case Manager = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::Patient => 'Pasien / Keluarga',
            self::Admin => 'Administrator',
            self::Coordinator => 'Koordinator Homecare',
            self::MedicalStaff => 'Tenaga Kesehatan',
            self::Manager => 'Manajer',
        };
    }

    /** Role yang bekerja di area operasional rumah sakit (/operasional). */
    public function isOperational(): bool
    {
        return in_array($this, [self::Admin, self::Coordinator, self::Manager], true);
    }

    /** Role yang dapat berkunjung ke pasien (punya tugas lapangan). */
    public function isFieldStaff(): bool
    {
        return $this === self::MedicalStaff;
    }
}
