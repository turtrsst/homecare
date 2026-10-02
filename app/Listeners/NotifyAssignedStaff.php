<?php

namespace App\Listeners;

use App\Events\StaffAssignedToAppointment;
use App\Notifications\StaffTaskAssigned;
use Illuminate\Events\Attribute\Listens;

/** Beri tahu setiap tenaga kesehatan (yang punya akun) tentang tugas baru. */
class NotifyAssignedStaff
{
    #[Listens(StaffAssignedToAppointment::class)]
    public function handle(StaffAssignedToAppointment $event): void
    {
        $event->appointment->loadMissing('assignments.staff.user', 'request.patient');

        foreach ($event->appointment->assignments as $assignment) {
            if ($assignment->status->value === 'cancelled') {
                continue;
            }

            $staffUser = $assignment->staff?->user;

            $staffUser?->notify(new StaffTaskAssigned($event->appointment));
        }
    }
}
