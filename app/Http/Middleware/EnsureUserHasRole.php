<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate kasar per grup route: `middleware('role:admin,coordinator')`.
 * Pemeriksaan halus tetap lewat Gate/Policy.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'Akun tidak diizinkan mengakses halaman ini.');
        }

        $allowed = array_map(
            fn (string $role) => UserRole::tryFrom($role)?->value,
            $roles,
        );

        if (! in_array($user->role?->value, $allowed, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
