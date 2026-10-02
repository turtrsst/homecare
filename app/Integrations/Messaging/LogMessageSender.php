<?php

namespace App\Integrations\Messaging;

use App\Integrations\Contracts\MessageSender;
use Illuminate\Support\Facades\Log;

/**
 * Mode pengembangan: pesan hanya dicatat ke log, tidak dikirim ke mana pun.
 * Jujur terhadap status integrasi (tidak berpura-pura terkirim).
 */
class LogMessageSender implements MessageSender
{
    public function send(string $to, string $message): bool
    {
        Log::channel(config('logging.default'))->info('[message-sender:log]', [
            'to' => $to,
            'message' => $message,
        ]);

        return true;
    }

    public function name(): string
    {
        return 'log';
    }
}
