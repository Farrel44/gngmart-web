<?php

namespace App\Providers;

use App\Models\Category;
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
        /**
         * Share categories ke semua views untuk footer
         * Memungkinkan footer mengakses $footerCategories
         * tanpa perlu pass dari setiap controller
         *
         * Guard: cek tabel ada sebelum query, agar tidak crash
         * saat migrate pertama kali atau saat tabel belum dibuat.
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
