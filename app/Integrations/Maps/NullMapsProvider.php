<?php

namespace App\Integrations\Maps;

use App\Integrations\Contracts\MapsProvider;

/**
 * Tanpa geocoding. Koordinat alamat diisi manual bila diperlukan.
 */
class NullMapsProvider implements MapsProvider
{
    public function geocode(string $address): ?array
    {
        return null;
    }

    public function name(): string
    {
        return 'null';
    }
}
