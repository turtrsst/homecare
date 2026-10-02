<?php

namespace App\Services;

use App\Models\HomecareService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Master data layanan — dibaca banyak halaman, jarang berubah.
 * Di-cache dan diinvalidasi oleh observer model.
 *
 * Catatan desain: yang disimpan ke cache hanya ARRAY atribut polos (tanpa
 * objek), lalu model dibangun ulang via hydrate(). Menyimpan objek (mis.
 * Collection berisi model) ke cache rawan gagal dipulihkan saat dibaca
 * (unserialize() menghasilkan __PHP_Incomplete_Class) di sebagian lingkungan.
 */
class HomecareCatalog
{
    private const CACHE_KEY = 'homecare.services.active';

    /** @return Collection<int, HomecareService> */
    public function activeServices(): Collection
    {
        $rows = Cache::remember(
            self::CACHE_KEY,
            now()->addHour(),
            fn () => HomecareService::query()->active()->ordered()->get()
                ->map(fn (HomecareService $service) => $service->getAttributes())
                ->all()
        );

        return HomecareService::hydrate($rows);
    }

    /** @return Collection<int, HomecareService> */
    public function featuredServices(int $limit = 4): Collection
    {
        return $this->activeServices()
            ->where('is_featured', true)
            ->take($limit)
            ->values();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}