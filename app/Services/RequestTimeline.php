<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\HomecareRequestStatus;
use App\Models\HomecareRequest;

/**
 * Membangun timeline ramah-manusia untuk pasien dari status teknis.
 * Pasien melihat "Pengajuan diterima → Sedang diverifikasi → ..." — bukan
 * sekadar "Status: APPROVED".
 */
class RequestTimeline
{
    /**
     * @return list<array{key: string, label: string, description: string|null, state: 'done'|'current'|'upcoming'|'failed', at: string|null}>
     */
    public function build(HomecareRequest $request): array
    {
        $status = $request->status;
        $appointment = $request->appointment;

        if ($status === HomecareRequestStatus::Rejected) {
            return $this->terminalTimeline($request, 'Pengajuan ditolak', $request->rejected_reason);
        }

        if ($status === HomecareRequestStatus::Cancelled) {
            return $this->terminalTimeline($request, 'Pengajuan dibatalkan', $request->cancellation_reason);
        }

        $steps = [
            $this->step('submitted', 'Pengajuan diterima', 'Kami telah menerima pengajuan Anda.',
                $this->rank($status) >= 1, $this->rank($status) === 1, $request->submitted_at),
            $this->step('verified', 'Sedang diverifikasi', 'Tim homecare memeriksa kelengkapan pengajuan.',
                $this->rank($status) >= 2, $this->rank($status) === 2, $request->verified_at),
            $this->step('approved', 'Homecare disetujui', 'Layanan yang Anda butuhkan telah ditetapkan.',
                $this->rank($status) >= 3, $this->rank($status) === 3, $request->screened_at),
            $this->step('scheduled', 'Petugas & jadwal ditetapkan', 'Tenaga kesehatan ditugaskan untuk kunjungan Anda.',
                $this->rank($status) >= 4, $this->rank($status) === 4, $appointment?->created_at),
        ];

        $visitRank = $this->visitRank($appointment?->status);

        $steps[] = $this->step('on_the_way', 'Petugas menuju lokasi', 'Petugas berangkat ke alamat Anda.',
            $visitRank >= 1 || $status === HomecareRequestStatus::Completed,
            $visitRank === 1, null);
        $steps[] = $this->step('in_service', 'Pelayanan berlangsung', 'Petugas memberikan pelayanan di lokasi.',
            $visitRank >= 2 || $status === HomecareRequestStatus::Completed,
            $visitRank === 2, $appointment?->checkin_at);
        $steps[] = $this->step('completed', 'Selesai', 'Pelayanan selesai. Terima kasih, semoga lekas sembuh.',
            $status === HomecareRequestStatus::Completed,
            $status === HomecareRequestStatus::Completed, $request->completed_at ?? $appointment?->checkout_at);

        if ($status === HomecareRequestStatus::NeedInformation) {
            // Sisipkan penanda "perlu informasi" sebagai langkah aktif.
            $steps[1]['state'] = 'failed';
            $steps[1]['label'] = 'Perlu informasi tambahan';
            $steps[1]['description'] = $request->information_request
                ?: 'Tim kami membutuhkan informasi tambahan dari Anda.';
        }

        return $steps;
    }

    /** @return list<array<string, mixed>> */
    private function terminalTimeline(HomecareRequest $request, string $title, ?string $reason): array
    {
        return [
            $this->step('submitted', 'Pengajuan diterima', null, true, false, $request->submitted_at),
            [
                'key' => 'terminal',
                'label' => $title,
                'description' => $reason,
                'state' => 'failed',
                'at' => ($request->cancelled_at ?? $request->verified_at ?? now())->toDateTimeString(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function step(string $key, string $label, ?string $description, bool $done, bool $current, $at = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'description' => $description,
            'state' => $current ? 'current' : ($done ? 'done' : 'upcoming'),
            'at' => $at ? \Illuminate\Support\Carbon::parse($at)->translatedFormat('j M Y, H.i') : null,
        ];
    }

    private function rank(HomecareRequestStatus $status): int
    {
        return match ($status) {
            HomecareRequestStatus::Draft => 0,
            HomecareRequestStatus::Submitted, HomecareRequestStatus::NeedInformation => 1,
            HomecareRequestStatus::UnderReview => 2,
            HomecareRequestStatus::Approved => 3,
            HomecareRequestStatus::Scheduled => 4,
            HomecareRequestStatus::InProgress, HomecareRequestStatus::Completed => 5,
            HomecareRequestStatus::Cancelled, HomecareRequestStatus::Rejected => -1,
        };
    }

    private function visitRank(?AppointmentStatus $status): int
    {
        return match ($status) {
            AppointmentStatus::OnTheWay, AppointmentStatus::CheckedIn => 1,
            AppointmentStatus::InService => 2,
            AppointmentStatus::Completed => 3,
            default => 0,
        };
    }
}
