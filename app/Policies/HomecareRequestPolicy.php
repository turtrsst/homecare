<?php

namespace App\Policies;

use App\Models\HomecareRequest;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class HomecareRequestPolicy
{
    /** Daftar pengajuan (area operasional). */
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('operational.access');
    }

    /**
     * Lihat detail: pemilik, staf operasional, atau tenaga kesehatan
     * yang TER-ASSIGN pada kunjungan terkait (bukan semua nakes).
     */
    public function view(User $user, HomecareRequest $request): bool
    {
        if ($user->id === $request->user_id) {
            return true;
        }

        if (Gate::forUser($user)->allows('operational.access')) {
            return true;
        }

        return $this->isAssignedStaff($user, $request);
    }

    public function verify(User $user, HomecareRequest $request): bool
    {
        return Gate::forUser($user)->allows('requests.verify') && $request->status->isOpen();
    }

    public function schedule(User $user, HomecareRequest $request): bool
    {
        return Gate::forUser($user)->allows('requests.schedule')
            && in_array($request->status->value, ['approved', 'scheduled'], true);
    }

    public function recordPayment(User $user, HomecareRequest $request): bool
    {
        return Gate::forUser($user)->allows('payments.manage');
    }

    /** Pasien boleh membatalkan sendiri hanya pada status awal. */
    public function cancel(User $user, HomecareRequest $request): bool
    {
        if ($user->id === $request->user_id) {
            return $request->status->cancellableByPatient();
        }

        return Gate::forUser($user)->allows('requests.verify');
    }

    /** Melengkapi informasi tambahan: hanya pemilik. */
    public function provideInformation(User $user, HomecareRequest $request): bool
    {
        return $user->id === $request->user_id
            && $request->status->value === 'need_information';
    }

    private function isAssignedStaff(User $user, HomecareRequest $request): bool
    {
        $staff = $user->staffProfile;

        if (! $staff) {
            return false;
        }

        return $request->appointment?->assignments()
            ->where('healthcare_staff_id', $staff->id)
            ->whereIn('status', ['assigned', 'active'])
            ->exists() ?? false;
    }
}
