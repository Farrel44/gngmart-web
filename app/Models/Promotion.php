<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model Promotion
 *
 * Sistem promo fleksibel yang bisa diterapkan ke produk atau kategori.
 * Mendukung persentase diskon, tanggal aktif, dan status on/off.
 *
 * Conflict resolution: jika beberapa promosi berlaku untuk satu produk,
 * yang digunakan adalah promo dengan discount_percentage tertinggi.
 */
class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'discount_percentage',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // === Relationships ===

    /**
     * Produk yang terdaftar langsung dalam promosi ini
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_product');
    }

    /**
     * Kategori yang seluruh produknya termasuk dalam promosi ini
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'promotion_category');
    }

    // === Scopes ===

    /**
     * Scope: hanya promosi yang sedang berlaku saat ini
     * Syarat: is_active = true, start_date <= hari ini, end_date >= hari ini
     */
    public function scopeCurrentlyActive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    /**
     * Cek apakah promosi ini sedang aktif pada saat ini
     */
    public function isCurrentlyActive(): bool
    {
        $today = now()->startOfDay();

        return $this->is_active
            && $this->start_date->startOfDay()->lte($today)
            && $this->end_date->startOfDay()->gte($today);
    }
}
