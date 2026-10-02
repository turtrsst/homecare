<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class HealthcareStaffPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('operational.access');
    }

    public function manage(User $user): bool
    {
        return Gate::forUser($user)->allows('staff.manage');
    }
}
