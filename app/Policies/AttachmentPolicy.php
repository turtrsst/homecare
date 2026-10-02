<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\HomecareRequest;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AttachmentPolicy
{
    /**
     * Unduh dokumen sensitif: pengunggah, pemilik entitas (pasien),
     * staf operasional, atau petugas ter-assign pada request terkait.
     */
    public function view(User $user, Attachment $attachment): bool
    {
        if ($user->id === $attachment->uploaded_by) {
            return true;
        }

        if (Gate::forUser($user)->allows('operational.access')) {
            return true;
        }

        $request = $this->resolveRequest($attachment);

        if (! $request) {
            return false;
        }

        return Gate::forUser($user)->allows('view', $request);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        if (Gate::forUser($user)->allows('operational.access')) {
            return true;
        }

        $request = $this->resolveRequest($attachment);

        return $request !== null
            && $user->id === $request->user_id
            && $request->status->isOpen();
    }

    private function resolveRequest(Attachment $attachment): ?HomecareRequest
    {
        $attachable = $attachment->attachable;

        return match (true) {
            $attachable instanceof HomecareRequest => $attachable,
            $attachable instanceof \App\Models\Appointment => $attachable->request,
            $attachable instanceof \App\Models\ServiceRecord => $attachable->appointment?->request,
            default => null,
        };
    }
}
