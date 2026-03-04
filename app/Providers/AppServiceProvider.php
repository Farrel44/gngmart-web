<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
         */
        $footerCategories = Category::orderBy('name', 'asc')->get();
        View::share('footerCategories', $footerCategories);
    }
}
