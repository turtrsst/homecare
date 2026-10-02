<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class HomecareServicePolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // katalog bersifat publik
    }

    public function manage(User $user): bool
    {
        return Gate::forUser($user)->allows('services.manage');
    }
}
