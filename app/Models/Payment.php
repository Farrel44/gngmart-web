<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    // ========================================
    // Payment Method Constants
    // ========================================
    public const METHOD_TRANSFER = 'transfer';  // Transfer bank
    public const METHOD_EWALLET = 'ewallet';    // E-wallet (OVO, GoPay, dll)
    public const METHOD_COD = 'cod';            // Cash on Delivery

    // ========================================
    // Payment Status Constants
    // ========================================
    public const STATUS_PENDING = 'pending';    // Menunggu pembayaran/verifikasi
    public const STATUS_SUCCESS = 'success';    // Pembayaran berhasil diverifikasi
    public const STATUS_FAILED = 'failed';      // Pembayaran gagal/ditolak

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_status',
        'payment_proof',
        'payment_date',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
        ];
    }

    // ========================================
    // Static Helper Methods
    // ========================================

    /**
     * Daftar semua metode pembayaran yang tersedia.
     */
    public static function getMethods(): array
    {
        return [
            self::METHOD_TRANSFER,
            self::METHOD_EWALLET,
            self::METHOD_COD,
        ];
    }

    /**
     * Label metode pembayaran untuk UI (bahasa Indonesia).
     */
    public static function getMethodLabels(): array
    {
        return [
            self::METHOD_TRANSFER => 'Transfer Bank',
            self::METHOD_EWALLET => 'E-Wallet',
            self::METHOD_COD => 'Bayar di Tempat (COD)',
        ];
    }

    /**
     * Daftar semua status pembayaran yang valid.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_SUCCESS,
            self::STATUS_FAILED,
        ];
    }

    /**
     * Label status pembayaran untuk UI (bahasa Indonesia).
     */
    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_SUCCESS => 'Berhasil',
            self::STATUS_FAILED => 'Gagal',
        ];
    }

    // ========================================
    // Instance Helper Methods
    // ========================================

    /**
     * Ambil label metode pembayaran saat ini.
     */
    public function getMethodLabel(): string
    {
        return self::getMethodLabels()[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Ambil label status pembayaran saat ini.
     */
    public function getStatusLabel(): string
    {
        return self::getStatusLabels()[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Cek apakah metode pembayaran memerlukan bukti bayar.
     * COD tidak perlu bukti, transfer & e-wallet perlu.
     */
    public function requiresProof(): bool
    {
        return $this->payment_method !== self::METHOD_COD;
    }

    /**
     * Cek apakah pembayaran sudah diverifikasi (success).
     */
    public function isVerified(): bool
    {
        return $this->payment_status === self::STATUS_SUCCESS;
    }

    /**
     * Cek apakah pembayaran masih pending (bisa diverifikasi admin).
     */
    public function isPending(): bool
    {
        return $this->payment_status === self::STATUS_PENDING;
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Relasi: payment milik satu order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
