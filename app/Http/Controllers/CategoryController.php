<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

/**
 * Controller untuk halaman kategori publik
 *
 * Menampilkan produk berdasarkan kategori.
 * Tidak memerlukan autentikasi.
 */
class CategoryController extends Controller
{
    /**
     * Tampilkan halaman produk per kategori
     *
     * Menggunakan route model binding untuk resolve Category by slug.
     * Produk di-eager load dengan images dan category untuk mencegah N+1.
     */
    public function show(Category $category): View
    {
        $products = Product::with(['category', 'images'])
            ->where('category_id', $category->id)
            ->where('stock', '>', 0)
            ->latest()
            ->get();

        return view('categories.show', compact('category', 'products'));
    }
}
