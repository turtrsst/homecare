<?php

namespace App\Integrations\Exceptions;

use RuntimeException;

/**
 * Dilempar ketika sebuah provider eksternal dipanggil padahal belum
 * dikonfigurasi/diaktifkan. Aplikasi standalone tidak boleh berpura-pura
 * terhubung ke sistem eksternal.
 */
class IntegrationNotConfiguredException extends RuntimeException
{
    public static function for(string $integration, string $reason = ''): self
    {
        return new self(sprintf(
            'Integrasi "%s" belum dikonfigurasi.%s',
            $integration,
            $reason ? ' '.$reason : ' Aktifkan pada menu Integrasi (admin) atau .env.'
        ));
    }
}
