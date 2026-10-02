<?php

namespace App\Actions;

use App\Models\HomecareRequest;
use App\Models\ServiceReview;
use App\Models\User;
use App\Services\AuditService;
use DomainException;

/** Simpan ulasan pasien (bintang 1–5) untuk petugas yang memeriksa. */
class StoreReviewAction
{
    public function __construct(private readonly AuditService $audit) {}

    /** @param array{rating: int, comment?: string|null} $data */
    public function execute(HomecareRequest $request, array $data, User $actor): ServiceReview
    {
        $request->loadMissing('review', 'appointment.assignments');

        if (! $request->canBeReviewed()) {
            throw new DomainException('Ulasan baru bisa diberikan setelah pelayanan selesai dan pembayaran lunas.');
        }

        if ($request->review !== null) {
            throw new DomainException('Anda sudah memberikan ulasan untuk pengajuan ini. Terima kasih!');
        }

        $staff = $request->appointment?->primaryStaff();

        $review = ServiceReview::create([
            'homecare_request_id' => $request->id,
            'appointment_id' => $request->appointment?->id,
            'healthcare_staff_id' => $staff?->id,
            'user_id' => $actor->id,
            'rating' => (int) $data['rating'],
            'comment' => filled($data['comment'] ?? null) ? trim($data['comment']) : null,
        ]);

        $this->audit->log('STORED_REVIEW', $request, 'Pasien memberikan ulasan '.$review->rating.' bintang', [
            'review_id' => $review->id,
            'rating' => $review->rating,
            'healthcare_staff_id' => $staff?->id,
        ], $actor);

        return $review;
    }
}
