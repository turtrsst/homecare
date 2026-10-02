<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Pencatatan audit trail terpusat.
 *
 * Aturan: jangan pernah memasukkan password/token/secret ke $properties.
 */
class AuditService
{
    public const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'token', 'api_key', 'secret',
        'remember_token', 'authorization', 'cookie',
    ];

    public function log(
        string $event,
        ?Model $auditable = null,
        ?string $description = null,
        array $properties = [],
        ?User $user = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => ($user ?? Auth::user())?->id,
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'description' => $description,
            'properties' => $this->sanitize($properties),
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 512),
            'created_at' => now(),
        ]);
    }

    /** Buang key sensitif dari metadata sebelum disimpan. */
    private function sanitize(array $properties): array
    {
        $clean = [];

        foreach ($properties as $key => $value) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                continue;
            }

            $clean[$key] = is_array($value) ? $this->sanitize($value) : $value;
        }

        return $clean;
    }
}
