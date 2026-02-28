<?php

namespace App\Models;

use App\Services\PriceCalculationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'stock',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description ?? '',
        ];
    }

    /**
     * Otomatis generate slug dari nama produk saat create/update
     */
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name') && !$product->isDirty('slug')) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Gunakan slug sebagai route key untuk URL yang SEO-friendly
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Relasi: produk milik satu kategori
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi: produk punya banyak gambar
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Relasi: produk ada di banyak cart item
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Relasi: produk ada di banyak order item
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relasi: promosi yang berlaku langsung untuk produk ini
     */
    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_product');
    }

    // === Helper Methods untuk Harga & Diskon ===

    /**
     * Cek apakah produk memiliki harga diskon yang valid.
     * Diskon valid jika: ada nilai, > 0, dan lebih kecil dari harga asli.
     */
    public function hasDiscount(): bool
    {
        return $this->discount_price !== null
            && $this->discount_price > 0
            && $this->discount_price < $this->price;
    }

    /**
     * Dapatkan harga efektif yang harus dibayar customer.
     *
     * Prioritas:
     * 1. Promosi aktif (persentase diskon tertinggi)
     * 2. discount_price manual (legacy support)
     * 3. price (harga asli)
     *
     * Menggunakan PriceCalculationService untuk logika bisnis terpusat.
     */
    public function getEffectivePrice(): float
    {
        return app(PriceCalculationService::class)->calculatePrice($this);
    }

    /**
     * Hitung persentase diskon efektif untuk ditampilkan di UI.
     * Memperhitungkan promosi aktif dan discount_price legacy.
     */
    public function getDiscountPercentage(): int
    {
        return app(PriceCalculationService::class)->getDiscountPercentage($this);
    }

    /**
     * Dapatkan promosi aktif terbaik (diskon tertinggi) untuk produk ini.
     * Memeriksa promosi langsung pada produk DAN promosi pada kategorinya.
     */
    public function getBestActivePromotion(): ?Promotion
    {
        return app(PriceCalculationService::class)->getBestPromotion($this);
    }
}
