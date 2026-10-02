<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case OnTheWay = 'on_the_way';
    case CheckedIn = 'checked_in';
    case InService = 'in_service';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Terjadwal',
            self::OnTheWay => 'Petugas Menuju Lokasi',
            self::CheckedIn => 'Petugas Tiba di Lokasi',
            self::InService => 'Pelayanan Berlangsung',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /** Label yang dilihat pasien. */
    public function patientLabel(): string
    {
        return match ($this) {
            self::Scheduled => 'Petugas akan datang sesuai jadwal',
            self::OnTheWay => 'Petugas sedang menuju lokasi Anda',
            self::CheckedIn => 'Petugas sudah tiba',
            self::InService => 'Pelayanan sedang berlangsung',
            self::Completed => 'Pelayanan selesai',
            self::Cancelled => 'Kunjungan dibatalkan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Scheduled => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            self::OnTheWay => 'bg-sky-50 text-sky-700 ring-sky-200',
            self::CheckedIn => 'bg-blue-50 text-blue-700 ring-blue-200',
            self::InService => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::Completed => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Cancelled => 'bg-slate-100 text-slate-600 ring-slate-200',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Scheduled => [self::OnTheWay, self::CheckedIn, self::Cancelled],
            self::OnTheWay => [self::CheckedIn, self::Cancelled],
            self::CheckedIn => [self::InService, self::Cancelled],
            self::InService => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /** Proyeksi ke status request induk. */
    public function toRequestStatus(): HomecareRequestStatus
    {
        return match ($this) {
            self::Scheduled => HomecareRequestStatus::Scheduled,
            self::OnTheWay, self::CheckedIn, self::InService => HomecareRequestStatus::InProgress,
            self::Completed => HomecareRequestStatus::Completed,
            self::Cancelled => HomecareRequestStatus::Cancelled,
        };
    }
}
