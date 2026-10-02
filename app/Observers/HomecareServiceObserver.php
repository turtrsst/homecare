<?php

namespace App\Observers;

use App\Models\HomecareService;
use App\Services\HomecareCatalog;

/** Invalidasi cache katalog layanan saat master data berubah. */
class HomecareServiceObserver
{
    public function __construct(private readonly HomecareCatalog $catalog) {}

    public function saved(HomecareService $service): void
    {
        $this->catalog->flush();
    }

    public function deleted(HomecareService $service): void
    {
        $this->catalog->flush();
    }

    public function restored(HomecareService $service): void
    {
        $this->catalog->flush();
    }
}
