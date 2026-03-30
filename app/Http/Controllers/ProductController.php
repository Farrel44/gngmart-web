<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk katalog produk publik
 *
 * Menangani browse katalog, filter, search, dan detail produk.
 * Semua method publik, tidak perlu autentikasi.
 */
class ProductController extends Controller
{
    /**
     * Tampilkan katalog produk dengan dukungan search, filter, dan sorting
     *
     * Query parameters yang didukung:
     * - search: kata kunci pencarian (nama/deskripsi)
     * - category: filter berdasarkan ID kategori
     * - sort: urutan (latest, price_low, price_high, name)
     */
    public function index(Request $request): View
    {
        // Base query: produk dengan stok > 0, eager load relasi
        $query = Product::with(['category', 'images', 'promotions', 'category.promotions'])
            ->where('stock', '>', 0);

        // Search: cari di nama dan deskripsi produk
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        // Sorting - default ke terbaru
        $sort = $request->input('sort', 'latest');
        $query = $this->applySorting($query, $sort);

        // Paginasi: 12 produk per halaman (kelipatan 3/4 untuk grid responsive)
        // @phpstan-ignore-next-line - withQueryString() exists on concrete LengthAwarePaginator
        $products = $query->paginate(12)->withQueryString();

        // Ambil semua kategori untuk dropdown filter
        $categories = Category::orderBy('name', 'asc')->get();

        // Untuk menampilkan nama kategori yang sedang difilter
        /** @var Category|null $currentCategory */
        $currentCategory = $categoryId
            ? Category::query()->find($categoryId)
            : null;

        return view('products.index', compact(
            'products',
            'categories',
            'currentCategory',
            'search',
            'sort'
        ));
    }

    /**
     * Tampilkan detail produk beserta related products
     *
     * Menggunakan slug untuk URL SEO-friendly.
     * Meta tags di-pass ke view untuk head section.
     *
     * @param  Product  $product  (auto-resolved by slug via getRouteKeyName)
     */
    public function show(Product $product): View
    {
        // Eager load images dan category
        $product->load(['images', 'category', 'promotions', 'category.promotions']);

        // Related products: produk lain dari kategori yang sama
        // Exclude produk yang sedang dilihat, limit 4 untuk performa
        $relatedProducts = Product::with(['category', 'images', 'promotions', 'category.promotions'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Apply sorting ke query berdasarkan parameter sort
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    private function applySorting(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->latest(), // 'latest' atau default
        };
    }
}
