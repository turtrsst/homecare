<?php

namespace App\Actions;

use App\Enums\HomecareRequestStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\StaffAssignedToAppointment;
use App\Models\Appointment;
use App\Models\HomecareRequest;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\AppointmentScheduled;
use App\Services\AuditService;
use App\Services\RequestStatusManager;
use App\Services\SchedulingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Tetapkan jadwal + tenaga kesehatan + konfirmasi biaya, lalu terbitkan
 * status TERJADWAL untuk pasien.
 */
class ScheduleHomecare
{
    public function __construct(
        private readonly RequestStatusManager $status,
        private readonly SchedulingService $scheduling,
        private readonly AuditService $audit,
    ) {}

    /**
     * @param  array<int>  $staffIds
     */
    public function execute(
        HomecareRequest $request,
        Carbon $scheduledAt,
        array $staffIds,
        ?string $notes,
        ?User $actor,
    ): Appointment {
        return DB::transaction(function () use ($request, $scheduledAt, $staffIds, $notes, $actor) {
            $appointment = $this->scheduling->schedule(
                $request, $scheduledAt, $staffIds, null, $notes, $actor,
            );

            // Konfirmasi biaya: pastikan ada tagihan tercatat untuk pengajuan ini.
            $this->ensureInvoice($request, $actor);

            $this->status->transitionTo($request, HomecareRequestStatus::Scheduled, $actor, [
                'appointment_id' => $appointment->id,
                'scheduled_at' => $scheduledAt->toDateTimeString(),
            ]);

            StaffAssignedToAppointment::dispatch($appointment, $actor);

            $request->user?->notify(new AppointmentScheduled($appointment));

            $this->audit->log('PUBLISHED_SCHEDULE', $request, 'Jadwal kunjungan diterbitkan ke pasien', [], $actor);

            return $appointment;
        });
    }

    private function ensureInvoice(HomecareRequest $request, ?User $actor): void
    {
        $hasInvoice = $request->payments()->exists();

        if (! $hasInvoice && (float) $request->total_amount > 0) {
            Payment::create([
                'homecare_request_id' => $request->id,
                'method' => PaymentMethod::Cash,
                'status' => PaymentStatus::Unpaid,
                'amount' => $request->total_amount,
                'notes' => 'Tagihan dibuat otomatis saat penjadwalan.',
                'received_by' => $actor?->id,
            ]);
        }
    }
}
