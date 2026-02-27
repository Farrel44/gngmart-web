<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

/**
 * Controller untuk halaman publik utama (landing page)
 * 
 * Menampilkan overview toko: kategori, produk unggulan, dll.
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
        // Ambil semua kategori yang punya produk (untuk navigasi kategori)
        $categories = Category::withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();

        // Ambil semua produk yang masih ada stok untuk featured/showcase
        // Eager load images untuk menghindari N+1 saat render card
        $featuredProducts = Product::with(['category', 'images'])
            ->where('stock', '>', 0)
            ->latest()
            ->get();

        return view('home', compact('categories', 'featuredProducts'));
    }
}
