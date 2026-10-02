<?php

namespace App\Integrations\Contracts;

/**
 * Geocoding/peta. Default: NullMapsProvider (tanpa geocoding).
 * Masa depan: GoogleMapsProvider.
 */
interface MapsProvider
{
    /**
     * Ubah alamat menjadi koordinat.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocode(string $address): ?array;

    public function name(): string;
}
