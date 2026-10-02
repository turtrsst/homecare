<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('operational.access')
            || $user->staffProfile !== null;
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if (Gate::forUser($user)->allows('operational.access')) {
            return true;
        }

        if ($user->id === $appointment->request?->user_id) {
            return true;
        }

        return $this->isAssigned($user, $appointment);
    }

    /** Kelola jadwal (ubah/assignment ulang): koordinator/admin. */
    public function manage(User $user, Appointment $appointment): bool
    {
        return Gate::forUser($user)->allows('requests.schedule');
    }

    /** Aksi lapangan (berangkat/check-in/pelayanan/selesai): hanya petugas ter-assign. */
    public function visit(User $user, Appointment $appointment): bool
    {
        return $this->isAssigned($user, $appointment);
    }

    private function isAssigned(User $user, Appointment $appointment): bool
    {
        $staff = $user->staffProfile;

        if (! $staff) {
            return false;
        }

        return $appointment->assignments()
            ->where('healthcare_staff_id', $staff->id)
            ->whereIn('status', ['assigned', 'active'])
            ->exists();
    }
}
