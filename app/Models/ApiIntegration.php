<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiIntegration extends Model
{
    protected $fillable = [
        'code',
        'name',
        'driver',
        'base_url',
        'is_active',
        'settings',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'encrypted:array',
            'last_used_at' => 'datetime',
        ];
    }

    public function externalReferences(): HasMany
    {
        return $this->hasMany(ExternalReference::class);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }
}
