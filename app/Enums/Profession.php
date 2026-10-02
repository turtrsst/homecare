<?php

namespace App\Enums;

enum Profession: string
{
    case Doctor = 'doctor';
    case Nurse = 'nurse';
    case Midwife = 'midwife';
    case Physiotherapist = 'physiotherapist';
    case Nutritionist = 'nutritionist';
    case LabTechnician = 'lab_technician';
    case Caregiver = 'caregiver';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Doctor => 'Dokter',
            self::Nurse => 'Perawat',
            self::Midwife => 'Bidan',
            self::Physiotherapist => 'Fisioterapis',
            self::Nutritionist => 'Ahli Gizi',
            self::LabTechnician => 'Analis Laboratorium',
            self::Caregiver => 'Pendamping Pasien',
            self::Other => 'Tenaga Kesehatan Lainnya',
        };
    }
}
