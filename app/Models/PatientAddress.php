<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_profile_id',
        'label',
        'recipient_name',
        'phone',
        'address_line',
        'city',
        'province',
        'postal_code',
        'notes',
        'latitude',
        'longitude',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_primary' => 'boolean',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function homecareRequests(): HasMany
    {
        return $this->hasMany(HomecareRequest::class);
    }

    public function oneLine(): string
    {
        $parts = array_filter([
            $this->address_line,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    /** Snapshot untuk disimpan pada appointment. */
    public function toSnapshot(): array
    {
        return [
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'phone' => $this->phone,
            'address_line' => $this->address_line,
            'city' => $this->city,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
            'notes' => $this->notes,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'one_line' => $this->oneLine(),
        ];
    }
}
