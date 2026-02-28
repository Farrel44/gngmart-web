<?php

namespace App\Http\Controllers;

use App\Models\CarouselSlide;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

/**
 * Controller untuk halaman publik utama (landing page)
 *
 * Menampilkan overview toko: carousel, kategori, produk unggulan.
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
        // Carousel slides: hanya yang aktif, diurutkan sesuai order_column
        $slides = CarouselSlide::active()->get();

        // Ambil semua kategori yang punya produk (untuk navigasi kategori)
        $categories = Category::withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();

        // Ambil produk yang masih ada stok untuk featured/showcase
        // Eager load images dan relasi yang diperlukan untuk price calculation
        $featuredProducts = Product::with(['category', 'images'])
            ->where('stock', '>', 0)
            ->latest()
            ->get();

        return view('home', compact('slides', 'categories', 'featuredProducts'));
    }
}
