<?php

namespace App\Observers;

use App\Models\Promotion;
use App\Services\CacheService;

class PromotionObserver
{
    public function saved(Promotion $promotion): void
    {
        CacheService::clearProductCache();
    }

    public function deleted(Promotion $promotion): void
    {
        CacheService::clearProductCache();
    }
}
