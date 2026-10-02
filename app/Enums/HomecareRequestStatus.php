<?php

namespace App\Enums;

enum HomecareRequestStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case NeedInformation = 'need_information';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Menunggu Verifikasi',
            self::UnderReview => 'Sedang Diverifikasi',
            self::NeedInformation => 'Perlu Informasi Tambahan',
            self::Approved => 'Disetujui',
            self::Scheduled => 'Terjadwal',
            self::InProgress => 'Sedang Berlangsung',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Rejected => 'Ditolak',
        };
    }

    /** Label ringkas untuk pasien (bahasa paling sederhana). */
    public function patientLabel(): string
    {
        return match ($this) {
            self::Draft => 'Belum Dikirim',
            self::Submitted, self::UnderReview => 'Sedang Diproses',
            self::NeedInformation => 'Butuh Informasi Anda',
            self::Approved => 'Disetujui',
            self::Scheduled => 'Terjadwal',
            self::InProgress => 'Petugas Sedang Bekerja',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Rejected => 'Ditolak',
        };
    }

    /** Kelas warna badge Tailwind. */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700 ring-slate-200',
            self::Submitted => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::UnderReview => 'bg-sky-50 text-sky-700 ring-sky-200',
            self::NeedInformation => 'bg-orange-50 text-orange-700 ring-orange-200',
            self::Approved => 'bg-teal-50 text-teal-700 ring-teal-200',
            self::Scheduled => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            self::InProgress => 'bg-blue-50 text-blue-700 ring-blue-200',
            self::Completed => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Cancelled => 'bg-slate-100 text-slate-600 ring-slate-200',
            self::Rejected => 'bg-rose-50 text-rose-700 ring-rose-200',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled, self::Rejected], true);
    }

    public function isOpen(): bool
    {
        return ! $this->isFinal();
    }

    /** Apakah pasien masih boleh membatalkan sendiri. */
    public function cancellableByPatient(): bool
    {
        return in_array($this, [
            self::Draft, self::Submitted, self::UnderReview,
            self::NeedInformation, self::Approved, self::Scheduled,
        ], true);
    }

    /**
     * Transisi status yang diizinkan.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Submitted, self::Cancelled],
            self::Submitted => [self::UnderReview, self::NeedInformation, self::Rejected, self::Cancelled],
            self::UnderReview => [self::Approved, self::NeedInformation, self::Rejected, self::Cancelled],
            self::NeedInformation => [self::Submitted, self::Cancelled],
            self::Approved => [self::Scheduled, self::Rejected, self::Cancelled],
            self::Scheduled => [self::InProgress, self::Completed, self::Cancelled],
            self::InProgress => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled, self::Rejected => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
