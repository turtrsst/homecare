<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomecareRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'homecare_request_id',
        'homecare_service_id',
        'service_name',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(HomecareRequest::class, 'homecare_request_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(HomecareService::class, 'homecare_service_id');
    }

    public function formattedUnitPrice(): string
    {
        return 'Rp '.number_format((float) $this->unit_price, 0, ',', '.');
    }

    public function formattedSubtotal(): string
    {
        return 'Rp '.number_format((float) $this->subtotal, 0, ',', '.');
    }
}
