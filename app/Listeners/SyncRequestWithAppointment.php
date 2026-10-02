<?php

namespace App\Listeners;

use App\Enums\AppointmentStatus;
use App\Enums\HomecareRequestStatus;
use App\Enums\StaffAssignmentStatus;
use App\Events\AppointmentStatusChanged;
use App\Events\HomecareRequestStatusChanged;
use Illuminate\Events\Attribute\Listens;
use Illuminate\Support\Facades\DB;

/**
 * Proyeksi status kunjungan ke status pengajuan induk
 * (on_the_way/checked_in/in_service → in_progress; completed → completed).
 */
class SyncRequestWithAppointment
{
    #[Listens(AppointmentStatusChanged::class)]
    public function handle(AppointmentStatusChanged $event): void
    {
        $appointment = $event->appointment;
        $request = $appointment->request;

        DB::transaction(function () use ($appointment, $request, $event) {
            $target = $event->to->toRequestStatus();

            if ($event->to === AppointmentStatus::CheckedIn) {
                $appointment->update(['checkin_at' => $appointment->checkin_at ?? now()]);
                $appointment->assignments()
                    ->where('status', StaffAssignmentStatus::Assigned->value)
                    ->update(['status' => StaffAssignmentStatus::Active->value]);
            }

            if ($event->to === AppointmentStatus::Completed) {
                $appointment->update(['checkout_at' => $appointment->checkout_at ?? now()]);
                $appointment->assignments()
                    ->whereIn('status', [StaffAssignmentStatus::Assigned->value, StaffAssignmentStatus::Active->value])
                    ->update(['status' => StaffAssignmentStatus::Completed->value]);
                $request->update(['completed_at' => now()]);
            }

            if ($event->to === AppointmentStatus::Cancelled) {
                $appointment->assignments()
                    ->whereIn('status', [StaffAssignmentStatus::Assigned->value, StaffAssignmentStatus::Active->value])
                    ->update(['status' => StaffAssignmentStatus::Cancelled->value]);
            }

            if (
                in_array($target, [HomecareRequestStatus::InProgress, HomecareRequestStatus::Completed], true)
                && $request->status->canTransitionTo($target)
            ) {
                $from = $request->status;
                $request->update(['status' => $target]);

                HomecareRequestStatusChanged::dispatch(
                    $request->refresh(),
                    $from,
                    $target,
                    $event->actor,
                    ['appointment_id' => $appointment->id],
                );
            }
        });
    }
}
