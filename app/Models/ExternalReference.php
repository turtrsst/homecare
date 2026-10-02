<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExternalReference extends Model
{
    protected $fillable = [
        'api_integration_id',
        'externalizable_type',
        'externalizable_id',
        'external_id',
        'external_type',
        'payload',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(ApiIntegration::class, 'api_integration_id');
    }

    public function externalizable(): MorphTo
    {
        return $this->morphTo();
    }
}
