<?php

namespace App\Models;

use App\Enums\Profession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HealthcareStaff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'healthcare_staff';

    protected $fillable = [
        'user_id',
        'name',
        'profession',
        'license_number',
        'specialization',
        'phone',
        'email',
        'is_active',
        'bio',
    ];

    protected function casts(): array
    {
        return [
            'profession' => Profession::class,
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ServiceReview::class);
    }

    /** Rata-rata bintang dari ulasan pasien (null bila belum ada ulasan). */
    public function averageRating(): ?float
    {
        $reviews = $this->reviews;

        if ($reviews->isEmpty()) {
            return null;
        }

        return round($reviews->avg('rating'), 1);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeProfession(Builder $query, Profession $profession): Builder
    {
        return $query->where('profession', $profession->value);
    }

    /** Sapaan profesional untuk ditampilkan ke pasien. */
    public function displayTitle(): string
    {
        return $this->profession->label();
    }
}
