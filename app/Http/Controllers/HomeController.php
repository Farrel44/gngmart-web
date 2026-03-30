<?php

namespace App\Http\Controllers;

use App\Models\CarouselSlide;
use App\Models\Category;
use App\Models\Product;
use App\Models\PromoBanner;
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
     * Query dioptimasi dengan eager loading untuk menghindari N+1
     * dan limit data agar halaman tetap ringan.
     */
    public function index(): View
    {
        $slides = CarouselSlide::active()->get();

        $promoSlide = PromoBanner::active()->first();

        $categories = Category::withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();

        $featuredProducts = Product::with(['category', 'images', 'promotions', 'category.promotions'])
            ->where('stock', '>', 0)
            ->latest()
            ->limit(12)
            ->get();

        return view('home', compact('slides', 'categories', 'featuredProducts', 'promoSlide'));
    }
}
