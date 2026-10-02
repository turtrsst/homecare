<?php

namespace App\Actions;

use App\Enums\HomecareRequestStatus;
use App\Models\HomecareRequest;
use App\Models\HomecareService;
use App\Models\User;
use App\Services\AuditService;
use App\Services\RequestStatusManager;
use Illuminate\Support\Facades\DB;

/**
 * Skrining homecare: koordinator memastikan layanan final yang akan diberikan
 * (dapat menambah/mengubah item layanan hasil rekomendasi skrining) lalu
 * menyetujui pengajuan.
 */
class ApproveHomecareRequest
{
    public function __construct(
        private readonly RequestStatusManager $status,
        private readonly AuditService $audit,
    ) {}

    /**
     * @param  array<int, array{homecare_service_id: int, quantity?: int, notes?: string|null}>|null  $finalServices
     */
    public function execute(
        HomecareRequest $request,
        ?string $screeningNotes,
        ?array $finalServices,
        User $actor,
    ): HomecareRequest {
        return DB::transaction(function () use ($request, $screeningNotes, $finalServices, $actor) {
            // Mulai review bila masih submitted (verifikasi + skrining satu langkah).
            if ($request->status === HomecareRequestStatus::Submitted) {
                $request->update(['verified_by' => $actor->id, 'verified_at' => now()]);
                $this->status->transitionTo($request, HomecareRequestStatus::UnderReview, $actor);
            }

            $request->update([
                'screened_by' => $actor->id,
                'screened_at' => now(),
                'screening_notes' => $screeningNotes,
            ]);

            if (is_array($finalServices) && count($finalServices) > 0) {
                $this->replaceItems($request, $finalServices);
            }

            $this->audit->log('SCREENED_HOMECARE', $request, 'Skrining homecare selesai', [
                'final_items' => $request->items()->count(),
            ], $actor);

            return $this->status->transitionTo($request->refresh(), HomecareRequestStatus::Approved, $actor, [
                'message' => 'Pengajuan Anda disetujui. Jadwal dan petugas sedang disiapkan.',
            ]);
        });
    }

    /** @param array<int, array<string, mixed>> $finalServices */
    private function replaceItems(HomecareRequest $request, array $finalServices): void
    {
        $request->items()->delete();

        $total = 0.0;

        foreach ($finalServices as $item) {
            $service = HomecareService::query()->active()->findOrFail($item['homecare_service_id']);
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $subtotal = (float) $service->price * $quantity;
            $total += $subtotal;

            $request->items()->create([
                'homecare_service_id' => $service->id,
                'service_name' => $service->name,
                'quantity' => $quantity,
                'unit_price' => $service->price,
                'subtotal' => $subtotal,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $request->update(['total_amount' => $total]);
    }
}
