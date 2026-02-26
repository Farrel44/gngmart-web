<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    /**
     * Relasi: cart milik satu user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: cart punya banyak cart item
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Hitung total harga semua items di cart.
     * Menggunakan effective price (discount jika ada).
     */
    public function getTotalPrice(): float
    {
        return $this->items->sum(fn (CartItem $item) => $item->getSubtotal());
    }

    /**
     * Hitung total jumlah items (sum of quantities).
     */
    public function getTotalItems(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Kosongkan cart dengan menghapus semua items.
     */
    public function clear(): void
    {
        $this->items()->delete();
    }
}
