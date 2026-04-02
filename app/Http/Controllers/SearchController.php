<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Produk populer/rekomendasi untuk ditampilkan saat search bar difokuskan
     * tanpa query. Mengembalikan array nama produk terlaris/terbaru.
     */
    public function popular(): JsonResponse
    {
        $names = Cache::remember('search_popular_products', 300, function () {
            return Product::where('stock', '>', 0)
                ->orderByDesc('id')
                ->limit(8)
                ->pluck('name')
                ->unique()
                ->values();
        });

        return response()->json($names);
    }

    /**
     * Autocomplete suggestions: mengembalikan array nama produk saja (string[]).
     * Menggunakan Scout database driver untuk full-text search.
     * Hasil di-deduplicate supaya tidak ada nama kembar.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $names = Product::search($query)
            ->query(fn ($q) => $q
                ->where('stock', '>', 0)
                ->orWhereHas('category', fn ($cat) => $cat->where('name', 'LIKE', "%{$query}%"))
            )
            ->take(15)
            ->get()
            ->pluck('name')
            ->unique()
            ->values()
            ->take(8);

        return response()->json($names);
    }

    /**
     * Halaman hasil pencarian lengkap dengan grid produk.
     * Menggunakan Scout + eager load category/images untuk performa.
     */
    public function results(Request $request): View
    {
        $query = $request->get('q', '');

        $products = collect();

        if (strlen($query) >= 1) {
            $products = Product::search($query)
                ->query(fn ($q) => $q
                    ->with(['category', 'images', 'promotions', 'category.promotions'])
                    ->where('stock', '>', 0)
                    ->orWhereHas('category', fn ($cat) => $cat->where('name', 'LIKE', "%{$query}%"))
                )
                ->get();
        }

        return view('search.results', compact('products', 'query'));
    }
}
