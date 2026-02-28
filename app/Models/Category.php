<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Gunakan slug sebagai route key untuk URL yang SEO-friendly
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Relasi: satu kategori punya banyak produk
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relasi: promosi yang berlaku untuk seluruh kategori ini
     */
    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_category');
    }
}
