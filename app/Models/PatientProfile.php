<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'nik',
        'gender',
        'birth_date',
        'blood_type',
        'relationship',
        'medical_notes',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(PatientContact::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(PatientAddress::class);
    }

    public function homecareRequests(): HasMany
    {
        return $this->hasMany(HomecareRequest::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'attachable_id')->where('attachable_type', self::class);
    }

    /** Hanya profil milik user tertentu. */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function age(): ?int
    {
        return $this->birth_date?->age;
    }

    public function primaryAddress(): ?PatientAddress
    {
        return $this->addresses->firstWhere('is_primary', true) ?? $this->addresses->first();
    }
}
