<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ClinicalNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'noteable_type',
        'noteable_id',
        'healthcare_staff_id',
        'user_id',
        'type',
        'content',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    public function noteable(): MorphTo
    {
        return $this->morphTo();
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(HealthcareStaff::class, 'healthcare_staff_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authorName(): string
    {
        return $this->staff?->name ?? $this->user?->name ?? 'Sistem';
    }
}
