<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'healthcare_staff_id',
        'systolic_bp',
        'diastolic_bp',
        'pulse',
        'respiratory_rate',
        'temperature_c',
        'oxygen_saturation',
        'consciousness',
        'pain_scale',
        'weight_kg',
        'height_cm',
        'findings',
        'payload',
        'assessed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'assessed_at' => 'datetime',
            'temperature_c' => 'float',
            'weight_kg' => 'float',
            'height_cm' => 'float',
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

    public function bloodPressure(): ?string
    {
        if ($this->systolic_bp && $this->diastolic_bp) {
            return $this->systolic_bp.'/'.$this->diastolic_bp.' mmHg';
        }

        return null;
    }
}
