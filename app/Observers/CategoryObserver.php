<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CacheService;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        CacheService::clearCategoryCache();
    }

    public function deleted(Category $category): void
    {
        CacheService::clearCategoryCache();
    }
}
