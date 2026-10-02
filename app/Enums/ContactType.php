<?php

namespace App\Enums;

enum ContactType: string
{
    case Phone = 'phone';
    case Whatsapp = 'whatsapp';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::Phone => 'Telepon',
            self::Whatsapp => 'WhatsApp',
            self::Email => 'Email',
        };
    }
}
