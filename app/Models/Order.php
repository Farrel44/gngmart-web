<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_price',
        'order_date',
        'order_status',
        'address_shipment',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'order_date' => 'datetime',
        ];
    }

    /**
     * Relasi: order milik satu user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: order punya banyak order item
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relasi: order punya satu payment
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
