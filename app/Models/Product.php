<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

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
     * Jika ada diskon valid, return harga diskon. Jika tidak, return harga normal.
     */
    public function getEffectivePrice(): float
    {
        return $this->hasDiscount() ? (float) $this->discount_price : (float) $this->price;
    }

    /**
     * Hitung persentase diskon untuk ditampilkan di UI.
     * Contoh: harga 100.000, diskon 75.000 → return 25 (%)
     */
    public function getDiscountPercentage(): int
    {
        if (! $this->hasDiscount()) {
            return 0;
        }

        return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
    }
}
