<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\CacheService;

class OrderObserver
{
    public function saved(Order $order): void
    {
        CacheService::clearAdminStatsCache();
    }
}
