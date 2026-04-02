<?php

namespace App\Observers;

use App\Models\CarouselSlide;
use App\Services\CacheService;

class CarouselSlideObserver
{
    public function saved(CarouselSlide $slide): void
    {
        CacheService::clearCarouselCache();
    }

    public function deleted(CarouselSlide $slide): void
    {
        CacheService::clearCarouselCache();
    }
}
