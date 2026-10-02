<?php

namespace App\Enums;

enum StaffAssignmentStatus: string
{
    case Assigned = 'assigned';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => 'Ditugaskan',
            self::Active => 'Sedang Bertugas',
            self::Completed => 'Selesai Bertugas',
            self::Cancelled => 'Tugas Dibatalkan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Assigned => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            self::Active => 'bg-blue-50 text-blue-700 ring-blue-200',
            self::Completed => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Cancelled => 'bg-slate-100 text-slate-600 ring-slate-200',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Assigned, self::Active], true);
    }
}
