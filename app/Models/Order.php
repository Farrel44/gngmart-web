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

    // Status constants — sesuai flow: pending → paid → processing → shipped → completed
    public const STATUS_PENDING = 'pending';       // Menunggu pembayaran
    public const STATUS_PAID = 'paid';             // Sudah bayar, menunggu diproses
    public const STATUS_PROCESSING = 'processing'; // Sedang diproses/dikemas
    public const STATUS_SHIPPED = 'shipped';       // Dalam pengiriman
    public const STATUS_COMPLETED = 'completed';   // Selesai (diterima)
    public const STATUS_CANCELLED = 'cancelled';   // Dibatalkan

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
     * Daftar semua status yang valid untuk validasi/dropdown.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PAID,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Label status untuk ditampilkan di UI (bahasa Indonesia).
     */
    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Pembayaran',
            self::STATUS_PAID => 'Sudah Dibayar',
            self::STATUS_PROCESSING => 'Diproses',
            self::STATUS_SHIPPED => 'Dikirim',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    /**
     * Ambil label status order saat ini.
     */
    public function getStatusLabel(): string
    {
        return self::getStatusLabels()[$this->order_status] ?? $this->order_status;
    }

    /**
     * Cek apakah order bisa dibatalkan.
     * Hanya bisa cancel jika masih pending (belum bayar).
     */
    public function canBeCancelled(): bool
    {
        return $this->order_status === self::STATUS_PENDING;
    }

    /**
     * Cek apakah order sudah selesai atau dibatalkan (final state).
     */
    public function isFinal(): bool
    {
        return in_array($this->order_status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ]);
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
