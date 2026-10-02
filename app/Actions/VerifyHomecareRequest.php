<?php

namespace App\Actions;

use App\Enums\HomecareRequestStatus;
use App\Models\HomecareRequest;
use App\Models\User;
use App\Services\RequestStatusManager;

/**
 * Verifikasi oleh admin/koordinator: terima (lanjut skrining),
 * minta informasi tambahan, atau tolak.
 */
class VerifyHomecareRequest
{
    public function __construct(private readonly RequestStatusManager $status) {}

    /** Mulai review formal (submitted → under_review). */
    public function startReview(HomecareRequest $request, User $actor): HomecareRequest
    {
        $request->update(['verified_by' => $actor->id, 'verified_at' => now()]);

        return $this->status->transitionTo($request, HomecareRequestStatus::UnderReview, $actor);
    }

    /** Minta informasi tambahan dari pasien. */
    public function requestInformation(HomecareRequest $request, string $questions, User $actor): HomecareRequest
    {
        $request->update([
            'verified_by' => $actor->id,
            'verified_at' => now(),
            'information_request' => $questions,
            'information_response' => null,
        ]);

        return $this->status->transitionTo($request, HomecareRequestStatus::NeedInformation, $actor);
    }

    /** Pasien melengkapi informasi → kembali ke antrean verifikasi. */
    public function provideInformation(HomecareRequest $request, string $answer, User $actor): HomecareRequest
    {
        $request->update(['information_response' => $answer]);

        return $this->status->transitionTo($request, HomecareRequestStatus::Submitted, $actor, [
            'message' => 'Informasi tambahan Anda telah diterima dan sedang diperiksa.',
        ]);
    }

    /** Tolak pengajuan dengan alasan yang jelas dan manusiawi. */
    public function reject(HomecareRequest $request, string $reason, User $actor): HomecareRequest
    {
        $request->update([
            'verified_by' => $actor->id,
            'verified_at' => now(),
            'rejected_reason' => $reason,
        ]);

        return $this->status->transitionTo($request, HomecareRequestStatus::Rejected, $actor, [
            'message' => $reason,
        ]);
    }
}
