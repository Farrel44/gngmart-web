<?php

namespace App\Observers;

use App\Models\PromoBanner;
use App\Services\CacheService;

class PromoBannerObserver
{
    public function saved(PromoBanner $banner): void
    {
        CacheService::clearPromoBannerCache();
    }

    public function deleted(PromoBanner $banner): void
    {
        CacheService::clearPromoBannerCache();
    }
}
