<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Promotion;

/**
 * Service untuk kalkulasi harga produk dengan sistem promosi
 *
 * Prioritas harga (dari tertinggi):
 * 1. Promosi aktif — discount_percentage tertinggi diterapkan ke harga asli
 * 2. discount_price manual pada produk (legacy / per-produk override)
 * 3. price (harga asli tanpa diskon)
 *
 * Conflict resolution:
 * Jika beberapa promosi berlaku (langsung ke produk + via kategori),
 * diambil yang memberikan discount_percentage paling besar.
 */
class PriceCalculationService
{
    /**
     * Hitung harga efektif yang harus dibayar customer
     */
    public function calculatePrice(Product $product): float
    {
        $bestPromo = $this->getBestPromotion($product);

        if ($bestPromo) {
            // Harga promo = harga asli dikurangi persentase diskon
            $promoPrice = (float) $product->price * (1 - $bestPromo->discount_percentage / 100);

            // Jika ada discount_price manual yang lebih murah, gunakan yang lebih murah
            if ($product->discount_price !== null
                && $product->discount_price > 0
                && $product->discount_price < $promoPrice
                && $product->discount_price < $product->price) {
                return (float) $product->discount_price;
            }

            return round($promoPrice, 2);
        }

        // Fallback ke discount_price legacy
        if ($product->discount_price !== null
            && $product->discount_price > 0
            && $product->discount_price < $product->price) {
            return (float) $product->discount_price;
        }

        // Harga asli
        return (float) $product->price;
    }

    /**
     * Hitung persentase diskon efektif untuk ditampilkan di badge/label
     */
    public function getDiscountPercentage(Product $product): int
    {
        $effectivePrice = $this->calculatePrice($product);
        $originalPrice = (float) $product->price;

        if ($effectivePrice >= $originalPrice || $originalPrice <= 0) {
            return 0;
        }

        return (int) round((($originalPrice - $effectivePrice) / $originalPrice) * 100);
    }

    /**
     * Dapatkan promosi aktif terbaik (persentase tertinggi) untuk produk tertentu
     *
     * Memeriksa dua sumber:
     * 1. Promosi yang di-assign langsung ke produk (promotion_product)
     * 2. Promosi yang di-assign ke kategori produk (promotion_category)
     *
     * Jika relasi sudah di-eager load (promotions, category.promotions),
     * gunakan collection filtering untuk menghindari N+1 queries.
     *
     * Return null jika tidak ada promosi aktif yang berlaku
     */
    public function getBestPromotion(Product $product): ?Promotion
    {
        $directPromo = $this->getDirectPromotion($product);
        $categoryPromo = $this->getCategoryPromotion($product);

        // Ambil yang memberikan diskon terbesar
        if ($directPromo && $categoryPromo) {
            return $directPromo->discount_percentage >= $categoryPromo->discount_percentage
                ? $directPromo
                : $categoryPromo;
        }

        return $directPromo ?? $categoryPromo;
    }

    /**
     * Cari promosi aktif yang di-assign langsung ke produk.
     * Gunakan relasi loaded jika tersedia, fallback ke query.
     */
    private function getDirectPromotion(Product $product): ?Promotion
    {
        if ($product->relationLoaded('promotions')) {
            return $product->promotions
                ->filter(fn (Promotion $p) => $p->isCurrentlyActive())
                ->sortByDesc('discount_percentage')
                ->first();
        }

        return Promotion::currentlyActive()
            ->whereHas('products', fn ($q) => $q->where('products.id', $product->id))
            ->orderByDesc('discount_percentage')
            ->first();
    }

    /**
     * Cari promosi aktif via kategori produk.
     * Gunakan relasi loaded jika tersedia, fallback ke query.
     */
    private function getCategoryPromotion(Product $product): ?Promotion
    {
        if (! $product->category_id) {
            return null;
        }

        if ($product->relationLoaded('category') && $product->category?->relationLoaded('promotions')) {
            return $product->category->promotions
                ->filter(fn (Promotion $p) => $p->isCurrentlyActive())
                ->sortByDesc('discount_percentage')
                ->first();
        }

        return Promotion::currentlyActive()
            ->whereHas('categories', fn ($q) => $q->where('categories.id', $product->category_id))
            ->orderByDesc('discount_percentage')
            ->first();
    }
}
