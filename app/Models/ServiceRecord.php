<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'healthcare_staff_id',
        'actions_taken',
        'results',
        'recommendations',
        'follow_up_needed',
        'follow_up_notes',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_needed' => 'boolean',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(HealthcareStaff::class, 'healthcare_staff_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
