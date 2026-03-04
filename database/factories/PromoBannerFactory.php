<?php

namespace Database\Factories;

use App\Models\PromoBanner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromoBanner>
 */
class PromoBannerFactory extends Factory
{
    protected $model = PromoBanner::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'subtitle' => 'PROMO SPESIAL',
            'description' => $this->faker->sentence(),
            'image_path' => null,
            'button_text' => 'Lihat Promo',
            'button_url' => '#',
            'background_color' => 'from-green-300 via-green-400 to-green-500',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
