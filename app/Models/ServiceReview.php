<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReview extends Model
{
    protected $fillable = [
        'homecare_request_id',
        'appointment_id',
        'healthcare_staff_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(HomecareRequest::class, 'homecare_request_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(HealthcareStaff::class, 'healthcare_staff_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return list<bool> posisi bintang yang terisi (kiri → kanan) */
    public function filledStars(): array
    {
        return array_map(fn (int $i) => $i <= $this->rating, range(1, 5));
    }

    public function label(): string
    {
        return match ($this->rating) {
            5 => 'Sangat Puas',
            4 => 'Puas',
            3 => 'Cukup',
            2 => 'Kurang',
            default => 'Sangat Kurang',
        };
    }
}
