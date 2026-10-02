<?php

namespace App\Integrations\Contracts;

/**
 * Kanal pesan singkat (WhatsApp/SMS). Default: LogMessageSender (mencatat).
 * Provider nyata: WhatsAppGatewaySender dsb.
 */
interface MessageSender
{
    /**
     * Kirim pesan teks ke nomor tujuan.
     *
     * @return bool true bila terkirim/diterima kanal
     */
    public function send(string $to, string $message): bool;

    public function name(): string;
}
