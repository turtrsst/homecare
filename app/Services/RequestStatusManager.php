<?php

namespace App\Services;

use App\Enums\HomecareRequestStatus;
use App\Events\HomecareRequestStatusChanged;
use App\Models\HomecareRequest;
use App\Models\User;
use DomainException;

/**
 * Satu-satunya tempat status pengajuan boleh berpindah.
 * Menegakkan state machine enum + memancarkan event (audit/notifikasi).
 */
class RequestStatusManager
{
    /**
     * @param  array<string, mixed>  $meta
     *
     * @throws DomainException bila transisi tidak diizinkan
     */
    public function transitionTo(
        HomecareRequest $request,
        HomecareRequestStatus $target,
        ?User $actor = null,
        array $meta = [],
    ): HomecareRequest {
        $from = $request->status;

        if ($from === $target) {
            return $request;
        }

        if (! $from->canTransitionTo($target)) {
            throw new DomainException(
                "Perubahan status dari «{$from->label()}» ke «{$target->label()}» tidak diizinkan."
            );
        }

        $request->update(['status' => $target]);

        HomecareRequestStatusChanged::dispatch($request->refresh(), $from, $target, $actor, $meta);

        return $request;
    }
}
