<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk Landing Page (HomeController)
 * 
 * Memastikan halaman home menampilkan kategori dan produk dengan benar.
 */
class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_can_be_rendered(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('GNGMart');
    }

    public function test_home_page_shows_categories_with_products(): void
    {
        // Arrange: buat kategori dengan produk
        $category = Category::factory()->create(['name' => 'Elektronik']);
        Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 10,
        ]);

        // Kategori tanpa produk (seharusnya tidak muncul)
        Category::factory()->create(['name' => 'Kosong']);

        // Act
        $response = $this->get(route('home'));

        // Assert: hanya kategori dengan produk yang muncul
        $response->assertStatus(200);
        $response->assertSee('Elektronik');
        $response->assertDontSee('Kosong');
    }

    public function test_home_page_shows_featured_products_with_stock(): void
    {
        // Arrange
        $category = Category::factory()->create();
        
        // Produk dengan stok (harus muncul)
        $availableProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Produk Tersedia',
            'stock' => 5,
        ]);
        
        // Produk tanpa stok (tidak muncul)
        $outOfStock = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Produk Habis',
            'stock' => 0,
        ]);

        // Act
        $response = $this->get(route('home'));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Produk Tersedia');
        $response->assertDontSee('Produk Habis');
    }

    public function test_home_page_limits_featured_products_to_eight(): void
    {
        // Arrange: buat 10 produk
        $category = Category::factory()->create();
        Product::factory()->count(10)->create([
            'category_id' => $category->id,
            'stock' => 5,
        ]);

        // Act
        $response = $this->get(route('home'));

        // Assert: max 8 produk ditampilkan
        $response->assertStatus(200);
        $this->assertLessThanOrEqual(8, substr_count($response->getContent(), 'product-card'));
    }

    // ========================================
    // GAP: Empty Database Rendering
    // ========================================

    public function test_home_renders_with_empty_database(): void
    {
        // Database kosong (no categories, no products)
        // Home page harus tetap render tanpa error
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('GNGMart');
    }
}
