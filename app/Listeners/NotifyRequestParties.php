<?php

namespace App\Listeners;

use App\Enums\HomecareRequestStatus;
use App\Events\HomecareRequestStatusChanged;
use App\Notifications\RequestNeedsInformation;
use App\Notifications\RequestStatusChanged;
use Illuminate\Events\Attribute\Listens;

/** Kirim notifikasi ke pasien/keluarga ketika status pengajuan berubah. */
class NotifyRequestParties
{
    #[Listens(HomecareRequestStatusChanged::class)]
    public function handle(HomecareRequestStatusChanged $event): void
    {
        $user = $event->request->user;

        if (! $user) {
            return;
        }

        // Hindari spam: draft tidak dinotifikasi.
        if ($event->to === HomecareRequestStatus::Draft) {
            return;
        }

        if ($event->to === HomecareRequestStatus::NeedInformation) {
            $user->notify(new RequestNeedsInformation(
                $event->request,
                $event->request->information_request ?: 'Mohon lengkapi informasi pada detail pengajuan.',
            ));

            return;
        }

        $user->notify(new RequestStatusChanged(
            $event->request,
            $event->to->label(),
            $event->meta['message'] ?? null,
        ));
    }
}
