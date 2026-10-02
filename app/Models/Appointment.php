<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'homecare_request_id',
        'status',
        'scheduled_at',
        'estimated_duration_minutes',
        'address_snapshot',
        'checkin_at',
        'checkout_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => AppointmentStatus::class,
            'scheduled_at' => 'datetime',
            'checkin_at' => 'datetime',
            'checkout_at' => 'datetime',
            'address_snapshot' => 'array',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(HomecareRequest::class, 'homecare_request_id');
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

    public function clinicalNotes(): MorphMany
    {
        return $this->morphMany(ClinicalNote::class, 'noteable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /** Petugas utama kunjungan. */
    public function primaryStaff(): ?HealthcareStaff
    {
        $assignment = $this->assignments
            ->sortBy(fn (StaffAssignment $a) => $a->role === 'primary' ? 0 : 1)
            ->firstWhere('status', '!=', 'cancelled')
            ?? $this->assignments->first();

        return $assignment?->staff;
    }

    public function scopeScheduledOn(Builder $query, $date): Builder
    {
        return $query->whereDate('scheduled_at', $date);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('scheduled_at', '>=', now())
            ->whereIn('status', [AppointmentStatus::Scheduled->value, AppointmentStatus::OnTheWay->value]);
    }

    public function scopeForStaff(Builder $query, HealthcareStaff $staff): Builder
    {
        return $query->whereHas('assignments', fn (Builder $q) => $q
            ->where('healthcare_staff_id', $staff->id)
            ->whereIn('status', ['assigned', 'active']));
    }

    public function formattedSchedule(): string
    {
        return $this->scheduled_at
            ->translatedFormat('l, j F Y \p\u\k\u\l H.i');
    }
}
