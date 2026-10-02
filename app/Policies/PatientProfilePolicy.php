<?php

namespace App\Policies;

use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PatientProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // selalu boleh melihat daftar pasien MILIKNYA (query dibatasi)
    }

    /** Pasien hanya bisa melihat profil miliknya; staf sesuai kewenangan. */
    public function view(User $user, PatientProfile $profile): bool
    {
        if ($user->id === $profile->user_id) {
            return true;
        }

        if (Gate::forUser($user)->allows('reports.view') || $user->role->value === 'admin' || $user->role->value === 'coordinator') {
            return true;
        }

        // Tenaga kesehatan: hanya pasien pada kunjungan yang ditugaskan kepadanya.
        $staff = $user->staffProfile;

        if (! $staff) {
            return false;
        }

        return $profile->homecareRequests()
            ->whereHas('appointment.assignments', function ($q) use ($staff) {
                $q->where('healthcare_staff_id', $staff->id)
                    ->whereIn('status', ['assigned', 'active']);
            })
            ->exists();
    }

    public function update(User $user, PatientProfile $profile): bool
    {
        return $user->id === $profile->user_id || $user->role->value === 'admin';
    }

    public function delete(User $user, PatientProfile $profile): bool
    {
        return $user->id === $profile->user_id && $profile->homecareRequests()->count() === 0;
    }
}
