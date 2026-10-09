<?php

namespace App\Actions;

use App\Enums\HomecareRequestStatus;
use App\Events\HomecareRequestStatusChanged;
use App\Models\HomecareRequest;
use App\Models\HomecareService;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Membuat pengajuan homecare dari data wizard booking (atau API) dan
 * langsung memasukkannya ke antrean verifikasi rumah sakit.
 */
class SubmitHomecareRequest
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * @param  array{
     *     patient_profile_id: int,
     *     patient_address_id: int,
     *     complaint?: string|null,
     *     notes?: string|null,
     *     preferred_date: string,
     *     preferred_time_window: string,
     *     services: array<int, array{homecare_service_id: int, quantity?: int, notes?: string|null}>
     * }  $data
     */
    public function execute(User $user, array $data): HomecareRequest
    {
        return DB::transaction(function () use ($user, $data) {
            $request = new HomecareRequest([
                'code' => $this->generateCode(),
                'user_id' => $user->id,
                'patient_profile_id' => $data['patient_profile_id'],
                'patient_address_id' => $data['patient_address_id'],
                'status' => HomecareRequestStatus::Draft,
                'complaint' => $data['complaint'] ?? null,
                'notes' => $data['notes'] ?? null,
                'preferred_date' => Carbon::parse($data['preferred_date'])->toDateString(),
                'preferred_time_window' => $data['preferred_time_window'],
                'submitted_at' => now(),
            ]);
            $request->save();

            $total = 0.0;

            foreach ($data['services'] as $item) {
                /** @var HomecareService $service */
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

            $request->update(['total_amount' => $total, 'status' => HomecareRequestStatus::Submitted]);

            HomecareRequestStatusChanged::dispatch(
                $request->refresh(),
                HomecareRequestStatus::Draft,
                HomecareRequestStatus::Submitted,
                $user,
                ['channel' => 'booking'],
            );

            $this->audit->log('SUBMITTED_HOMECARE', $request, 'Pengajuan homecare dibuat', [
                'total_amount' => $total,
                'service_count' => count($data['services']),
            ], $user);

            return $request->load(['items.service', 'patient', 'address']);
        });
    }

    /** Kode unik: HC-YYYYMM-XXXX (urut per bulan). */
    private function generateCode(): string
    {
        $prefix = 'HC-'.now()->format('Ym').'-';

        // Retry logic untuk handle race condition
        $maxAttempts = 5;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $last = HomecareRequest::query()
                ->where('code', 'like', $prefix.'%')
                ->orderByDesc('code')
                ->value('code');

            $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;
            $code = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

            // Check if code already exists
            if (!HomecareRequest::query()->where('code', $code)->exists()) {
                return $code;
            }

            // Wait sebelum retry
            usleep(100000); // 100ms
        }

        // Fallback: gunakan timestamp
        return $prefix.now()->format('His');
    }
}
