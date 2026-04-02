<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Hapus cache yang terkait produk (homepage, filter, search popular).
     * Dipanggil saat admin menambah/update/hapus produk atau promosi.
     */
    public static function clearProductCache(): void
    {
        Cache::forget('home_featured_products');
        Cache::forget('search_popular_products');
        Cache::forget('home_categories'); // categories with product count
    }

    /**
     * Hapus cache yang terkait kategori (footer, filter, homepage).
     * Dipanggil saat admin menambah/update/hapus kategori.
     */
    public static function clearCategoryCache(): void
    {
        Cache::forget('footer_categories');
        Cache::forget('product_filter_categories');
        Cache::forget('home_categories');
    }

    /**
     * Hapus cache carousel slides.
     */
    public static function clearCarouselCache(): void
    {
        Cache::forget('home_carousel_slides');
    }

    /**
     * Hapus cache promo banner.
     */
    public static function clearPromoBannerCache(): void
    {
        Cache::forget('home_promo_banner');
    }

    /**
     * Hapus cache statistik admin dashboard.
     */
    public static function clearAdminStatsCache(): void
    {
        Cache::forget('admin_store_stats');
        Cache::forget('admin_order_chart_7days_raw');
        Cache::forget('admin_order_status_chart');
        Cache::forget('admin_recent_activities');
    }
}
