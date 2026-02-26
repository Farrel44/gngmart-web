<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory untuk model Product — digunakan di testing dan seeder
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10000, 500000),
            'discount_price' => null, // Default: tidak ada diskon
            'stock' => fake()->numberBetween(0, 100),
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
        ];
    }

    /**
     * State: produk dengan harga diskon (10-50% off dari harga asli)
     */
    public function withDiscount(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price'] ?? fake()->randomFloat(2, 10000, 500000);
            // Diskon antara 10% - 50%
            $discountPercent = fake()->numberBetween(10, 50) / 100;
            $discountPrice = round($price * (1 - $discountPercent), 2);

            return [
                'price' => $price,
                'discount_price' => $discountPrice,
            ];
        });
    }

    /**
     * State: produk dengan SEO fields terisi lengkap
     */
    public function withSeo(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_title' => fake()->sentence(4),
            'meta_description' => fake()->text(150),
            'meta_keywords' => implode(', ', fake()->words(5)),
        ]);
    }

    /**
     * State: produk yang stoknya habis
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
