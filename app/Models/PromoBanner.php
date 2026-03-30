<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model PromoBanner
 *
 * Representasi promo banner section (bagian hijau di bawah carousel).
 * Dikelola admin via Filament panel, terpisah dari carousel_slides.
 */
class PromoBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image_path',
        'button_text',
        'button_url',
        'background_color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope: hanya banner yang aktif
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Mapping warna ke CSS gradient
     */
    private static array $gradientMap = [
        'from-green-300 via-green-400 to-green-500' => 'linear-gradient(to right, rgb(134, 239, 172), rgb(74, 222, 128), rgb(34, 197, 94))',
        'from-blue-300 via-blue-400 to-blue-500' => 'linear-gradient(to right, rgb(147, 197, 253), rgb(96, 165, 250), rgb(59, 130, 246))',
        'from-red-300 via-red-400 to-red-500' => 'linear-gradient(to right, rgb(252, 165, 165), rgb(248, 113, 113), rgb(239, 68, 68))',
        'from-yellow-300 via-yellow-400 to-yellow-500' => 'linear-gradient(to right, rgb(253, 224, 71), rgb(250, 204, 21), rgb(234, 179, 8))',
        'from-purple-300 via-purple-400 to-purple-500' => 'linear-gradient(to right, rgb(216, 180, 254), rgb(192, 132, 250), rgb(168, 85, 247))',
        'from-pink-300 via-pink-400 to-pink-500' => 'linear-gradient(to right, rgb(249, 168, 212), rgb(244, 114, 182), rgb(236, 72, 153))',
    ];

    /**
     * Get gradient CSS style untuk inline style
     */
    public function getGradientStyle(): string
    {
        return self::$gradientMap[$this->background_color]
            ?? self::$gradientMap['from-green-300 via-green-400 to-green-500'];
    }
}
