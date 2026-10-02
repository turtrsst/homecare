<?php

namespace App\Events;

use App\Enums\HomecareRequestStatus;
use App\Models\HomecareRequest;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dipancarkan setiap kali status pengajuan homecare berubah.
 * Listener: audit trail + notifikasi pasien/pihak terkait.
 */
class HomecareRequestStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly HomecareRequest $request,
        public readonly HomecareRequestStatus $from,
        public readonly HomecareRequestStatus $to,
        public readonly ?User $actor = null,
        public readonly array $meta = [],
    ) {}
}
