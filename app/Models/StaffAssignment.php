<?php

namespace App\Models;

use App\Enums\StaffAssignmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'healthcare_staff_id',
        'status',
        'role',
        'assigned_by',
        'assigned_at',
        'confirmed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => StaffAssignmentStatus::class,
            'assigned_at' => 'datetime',
            'confirmed_at' => 'datetime',
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

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
