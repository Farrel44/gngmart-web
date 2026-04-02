<?php

namespace App\Http\Controllers;

use App\Models\CarouselSlide;
use App\Models\Category;
use App\Models\Product;
use App\Models\PromoBanner;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Controller untuk halaman publik utama (landing page)
 *
 * Menampilkan overview toko: carousel, kategori, produk unggulan, dan promo banner.
 * Tidak memerlukan autentikasi.
 */
class HomeController extends Controller
{
    /**
     * Tampilkan landing page dengan konten dinamis
     *
     * Data di-cache 5 menit karena jarang berubah dan diakses setiap kunjungan.
     * Cache otomatis di-flush saat admin mengubah data via Filament (lihat Observer).
     */
    public function index(): View
    {
        $slides = Cache::remember('home_carousel_slides', 300, function () {
            return CarouselSlide::active()->get();
        });

        $promoSlide = Cache::remember('home_promo_banner', 300, function () {
            return PromoBanner::active()->first();
        });

        $categories = Cache::remember('home_categories', 300, function () {
            return Category::withCount('products')
                ->having('products_count', '>', 0)
                ->orderBy('name')
                ->get();
        });

        $featuredProducts = Cache::remember('home_featured_products', 300, function () {
            return Product::with(['category', 'images', 'promotions', 'category.promotions'])
                ->where('stock', '>', 0)
                ->latest()
                ->limit(12)
                ->get();
        });

        return view('home', compact('slides', 'categories', 'featuredProducts', 'promoSlide'));
    }
}
