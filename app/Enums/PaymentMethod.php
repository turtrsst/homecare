<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case Qris = 'qris';
    case Insurance = 'insurance';
    case Gateway = 'gateway';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Transfer => 'Transfer Bank',
            self::Qris => 'QRIS',
            self::Insurance => 'Asuransi / Penjaminan',
            self::Gateway => 'Pembayaran Online',
        };
    }
}
