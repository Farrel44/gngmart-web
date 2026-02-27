<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite untuk Product model helper methods.
 *
 * Covers:
 * - hasDiscount() edge cases
 * - getEffectivePrice() with/without discount
 * - getDiscountPercentage() calculation
 * - Slug auto-generation on create/update
 */
class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
    }

    // ========================================
    // hasDiscount() Tests
    // ========================================

    public function test_has_discount_returns_true_when_discount_is_valid(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => 80000,
        ]);

        $this->assertTrue($product->hasDiscount());
    }

    public function test_has_discount_returns_false_when_no_discount(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => null,
        ]);

        $this->assertFalse($product->hasDiscount());
    }

    public function test_has_discount_returns_false_when_discount_is_zero(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => 0,
        ]);

        $this->assertFalse($product->hasDiscount());
    }

    public function test_has_discount_returns_false_when_discount_exceeds_price(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => 150000,
        ]);

        $this->assertFalse($product->hasDiscount());
    }

    public function test_has_discount_returns_false_when_discount_equals_price(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => 100000,
        ]);

        $this->assertFalse($product->hasDiscount());
    }

    // ========================================
    // getEffectivePrice() Tests
    // ========================================

    public function test_get_effective_price_returns_discount_price_when_valid(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => 75000,
        ]);

        $this->assertEquals(75000, $product->getEffectivePrice());
    }

    public function test_get_effective_price_returns_normal_price_without_discount(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => null,
        ]);

        $this->assertEquals(100000, $product->getEffectivePrice());
    }

    public function test_get_effective_price_returns_normal_price_when_discount_invalid(): void
    {
        // Discount lebih besar dari harga → tidak valid → pakai harga normal
        $product = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => 150000,
        ]);

        $this->assertEquals(100000, $product->getEffectivePrice());
    }

    // ========================================
    // getDiscountPercentage() Tests
    // ========================================

    public function test_get_discount_percentage_calculation(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => 75000,
        ]);

        // (100000 - 75000) / 100000 × 100 = 25%
        $this->assertEquals(25, $product->getDiscountPercentage());
    }

    public function test_get_discount_percentage_returns_zero_without_discount(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => null,
        ]);

        $this->assertEquals(0, $product->getDiscountPercentage());
    }

    public function test_get_discount_percentage_rounds_correctly(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => 66666,
        ]);

        // (100000 - 66666) / 100000 × 100 = 33.334 → rounded to 33
        $this->assertEquals(33, $product->getDiscountPercentage());
    }

    // ========================================
    // Slug Auto-generation Tests
    // ========================================

    public function test_product_slug_auto_generated_on_create(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'name' => 'Laptop Gaming Terbaik',
            'slug' => null, // Slug kosong → harus di-generate otomatis
        ]);

        $this->assertEquals('laptop-gaming-terbaik', $product->slug);
    }

    public function test_product_slug_updated_on_name_change(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'name' => 'Original Name',
        ]);

        $product->update(['name' => 'Updated Product Name']);

        $this->assertEquals('updated-product-name', $product->fresh()->slug);
    }

    public function test_product_slug_preserved_when_set_manually(): void
    {
        $product = Product::factory()->for($this->category)->create([
            'name' => 'Some Product',
            'slug' => 'custom-slug',
        ]);

        $this->assertEquals('custom-slug', $product->slug);
    }

    // ========================================
    // Route Key Name Tests
    // ========================================

    public function test_product_uses_slug_as_route_key(): void
    {
        $product = new Product();

        $this->assertEquals('slug', $product->getRouteKeyName());
    }
}
