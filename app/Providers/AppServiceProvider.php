<?php

namespace App\Providers;

use App\Models\CarouselSlide;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoBanner;
use App\Models\Promotion;
use App\Observers\CarouselSlideObserver;
use App\Observers\CategoryObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\PromoBannerObserver;
use App\Observers\PromotionObserver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers untuk cache invalidation otomatis
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
        CarouselSlide::observe(CarouselSlideObserver::class);
        PromoBanner::observe(PromoBannerObserver::class);
        Promotion::observe(PromotionObserver::class);
        Order::observe(OrderObserver::class);

        /**
         * Share categories ke semua views untuk footer
         * Memungkinkan footer mengakses $footerCategories
         * tanpa perlu pass dari setiap controller
         */
        try {
            $footerCategories = Schema::hasTable('categories')
                ? Cache::remember('footer_categories', 3600, fn () => Category::orderBy('name', 'asc')->get())
                : collect();
        } catch (\Throwable) {
            $footerCategories = collect();
        }

        View::share('footerCategories', $footerCategories);
    }
}
