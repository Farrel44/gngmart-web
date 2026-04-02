<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\CacheService;

class ProductObserver
{
    public function saved(Product $product): void
    {
        CacheService::clearProductCache();
    }

    public function deleted(Product $product): void
    {
        CacheService::clearProductCache();
    }
}
