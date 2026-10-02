<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\StaffAssignmentStatus;
use App\Models\Appointment;
use App\Models\HealthcareStaff;
use App\Models\HomecareRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Penjadwalan kunjungan & assignment tenaga kesehatan.
 */
class SchedulingService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * Buat/perbarui jadwal kunjungan untuk pengajuan yang sudah disetujui.
     *
     * @param  array<int>  $staffIds
     */
    public function schedule(
        HomecareRequest $request,
        Carbon $scheduledAt,
        array $staffIds,
        ?int $durationMinutes = null,
        ?string $notes = null,
        ?User $actor = null,
    ): Appointment {
        return DB::transaction(function () use ($request, $scheduledAt, $staffIds, $durationMinutes, $notes, $actor) {
            $appointment = $request->appointment ?? new Appointment(['homecare_request_id' => $request->id]);

            $appointment->fill([
                'status' => $appointment->exists ? $appointment->status : AppointmentStatus::Scheduled,
                'scheduled_at' => $scheduledAt,
                'estimated_duration_minutes' => $durationMinutes
                    ?? $request->items->sum(fn ($i) => ($i->service?->duration_minutes ?? 60) * $i->quantity)
                    ?: 60,
                'address_snapshot' => $request->address?->toSnapshot(),
                'notes' => $notes,
            ]);
            $appointment->save();
            $appointment->loadMissing('request.address');

            $this->syncAssignments($appointment, $staffIds, $actor);

            $this->audit->log('SCHEDULED_HOMECARE', $request, 'Jadwal kunjungan ditetapkan', [
                'appointment_id' => $appointment->id,
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'staff_ids' => $staffIds,
            ], $actor);

            return $appointment;
        });
    }

    /** @param array<int> $staffIds */
    private function syncAssignments(Appointment $appointment, array $staffIds, ?User $actor): void
    {
        // Batalkan assignment yang dihapus dari daftar.
        $appointment->assignments()
            ->whereNotIn('healthcare_staff_id', $staffIds ?: [0])
            ->whereIn('status', [StaffAssignmentStatus::Assigned->value])
            ->update(['status' => StaffAssignmentStatus::Cancelled->value]);

        $first = true;

        foreach ($staffIds as $staffId) {
            $staff = HealthcareStaff::query()->active()->find($staffId);
            if (! $staff) {
                continue;
            }

            $assignment = $appointment->assignments()->firstOrNew([
                'appointment_id' => $appointment->id,
                'healthcare_staff_id' => $staff->id,
            ]);

            if (! $assignment->exists) {
                $assignment->fill([
                    'status' => StaffAssignmentStatus::Assigned,
                    'role' => $first ? 'primary' : 'support',
                    'assigned_by' => $actor?->id,
                    'assigned_at' => now(),
                ])->save();
            } elseif ($assignment->status === StaffAssignmentStatus::Cancelled) {
                $assignment->update([
                    'status' => StaffAssignmentStatus::Assigned,
                    'role' => $first ? 'primary' : 'support',
                    'assigned_by' => $actor?->id,
                    'assigned_at' => now(),
                ]);
            }

            $first = false;
        }
    }

    /** Staf yang tersedia untuk profesi tertentu (untuk form assignment). */
    public function availableStaff(?string $profession = null)
    {
        return HealthcareStaff::query()
            ->active()
            ->when($profession, fn ($q) => $q->where('profession', $profession))
            ->orderBy('name')
            ->get();
    }
}
