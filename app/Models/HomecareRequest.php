<?php

namespace App\Models;

use App\Enums\HomecareRequestStatus;
use App\Enums\PaymentStatus;
use App\Enums\TimeWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class HomecareRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'patient_profile_id',
        'patient_address_id',
        'status',
        'complaint',
        'notes',
        'preferred_date',
        'preferred_time_window',
        'verified_by',
        'verified_at',
        'rejected_reason',
        'information_request',
        'information_response',
        'screened_by',
        'screened_at',
        'screening_notes',
        'total_amount',
        'payment_status',
        'submitted_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => HomecareRequestStatus::class,
            'payment_status' => PaymentStatus::class,
            'preferred_time_window' => TimeWindow::class,
            'preferred_date' => 'date',
            'verified_at' => 'datetime',
            'screened_at' => 'datetime',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    /* ----------------------------------------------------------------- */
    /* Relasi                                                             */
    /* ----------------------------------------------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(PatientAddress::class, 'patient_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HomecareRequestItem::class);
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(ServiceReview::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function screener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'screened_by');
    }

    /* ----------------------------------------------------------------- */
    /* Scopes                                                             */
    /* ----------------------------------------------------------------- */

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeStatus(Builder $query, HomecareRequestStatus|string|array $status): Builder
    {
        $values = is_array($status)
            ? array_map(fn ($s) => $s instanceof HomecareRequestStatus ? $s->value : $s, $status)
            : [$status instanceof HomecareRequestStatus ? $status->value : $status];

        return $query->whereIn('status', $values);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            HomecareRequestStatus::Completed->value,
            HomecareRequestStatus::Cancelled->value,
            HomecareRequestStatus::Rejected->value,
            HomecareRequestStatus::Draft->value,
        ]);
    }

    /* ----------------------------------------------------------------- */
    /* Perilaku                                                           */
    /* ----------------------------------------------------------------- */

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function recalculateTotal(): void
    {
        $this->total_amount = $this->items->sum(fn (HomecareRequestItem $item) => (float) $item->subtotal);
        $this->save();
    }

    public function formattedTotal(): string
    {
        return 'Rp '.number_format((float) $this->total_amount, 0, ',', '.');
    }

    public function preferredWindowLabel(): ?string
    {
        return $this->preferred_time_window?->label();
    }

    /**
     * Pasien boleh memberi ulasan bila kunjungan sudah selesai DAN biaya
     * sudah lunas (pengajuan tanpa biaya dianggap lunas otomatis).
     */
    public function canBeReviewed(): bool
    {
        if ($this->status !== HomecareRequestStatus::Completed) {
            return false;
        }

        if ($this->payment_status->isSettled()) {
            return true;
        }

        return (float) $this->total_amount <= 0;
    }

    /** Status kunjungan efektif (menggabungkan appointment bila ada). */
    public function effectiveStatus(): HomecareRequestStatus
    {
        return $this->status;
    }
}
