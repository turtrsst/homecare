<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case Waived = 'waived';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Bayar',
            self::Partial => 'Dibayar Sebagian',
            self::Paid => 'Lunas',
            self::Failed => 'Gagal',
            self::Refunded => 'Dikembalikan',
            self::Waived => 'Dibebaskan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Unpaid => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::Partial => 'bg-sky-50 text-sky-700 ring-sky-200',
            self::Paid => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Failed => 'bg-rose-50 text-rose-700 ring-rose-200',
            self::Refunded => 'bg-slate-100 text-slate-600 ring-slate-200',
            self::Waived => 'bg-teal-50 text-teal-700 ring-teal-200',
        };
    }

    public function isSettled(): bool
    {
        return in_array($this, [self::Paid, self::Waived, self::Refunded], true);
    }
}
