<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /* ----------------------------------------------------------------- */
    /* Relasi                                                             */
    /* ----------------------------------------------------------------- */

    public function patientProfiles(): HasMany
    {
        return $this->hasMany(PatientProfile::class);
    }

    public function homecareRequests(): HasMany
    {
        return $this->hasMany(HomecareRequest::class);
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(HealthcareStaff::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /* ----------------------------------------------------------------- */
    /* Helper                                                             */
    /* ----------------------------------------------------------------- */

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isPatient(): bool
    {
        return $this->role === UserRole::Patient;
    }

    public function isOperational(): bool
    {
        return $this->role?->isOperational() ?? false;
    }

    /** Nama panggilan (kata pertama) untuk sapaan ramah. */
    public function firstName(): string
    {
        return trim(explode(' ', (string) $this->name)[0] ?? '');
    }

    /** Profil tenaga kesehatan bila user ini adalah nakes. */
    public function healthcareStaff(): ?HealthcareStaff
    {
        return $this->staffProfile;
    }
}
