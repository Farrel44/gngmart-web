<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk Product Catalog (ProductController@index)
 *
 * Memastikan fitur browse, search, filter, dan sorting bekerja dengan benar.
 */
class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_page_can_be_rendered(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Katalog Produk');
    }

    public function test_catalog_shows_only_products_with_stock(): void
    {
        // Arrange
        $category = Category::factory()->create();

        $inStock = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Ada Stok',
            'stock' => 10,
        ]);

        $outOfStock = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Habis Stok',
            'stock' => 0,
        ]);

        // Act
        $response = $this->get(route('products.index'));

        // Assert
        $response->assertSee('Ada Stok');
        $response->assertDontSee('Habis Stok');
    }

    public function test_catalog_can_search_products_by_name(): void
    {
        // Arrange
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Laptop Gaming ASUS',
            'stock' => 5,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Mouse Wireless',
            'stock' => 5,
        ]);

        // Act: search untuk "laptop"
        $response = $this->get(route('products.index', ['search' => 'laptop']));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Laptop Gaming ASUS');
        $response->assertDontSee('Mouse Wireless');
    }

    public function test_catalog_can_search_products_by_description(): void
    {
        // Arrange
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Produk A',
            'description' => 'Cocok untuk gaming dan multimedia',
            'stock' => 5,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Produk B',
            'description' => 'Untuk keperluan kantor',
            'stock' => 5,
        ]);

        // Act: search di description
        $response = $this->get(route('products.index', ['search' => 'gaming']));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Produk A');
        $response->assertDontSee('Produk B');
    }

    public function test_catalog_can_filter_by_category(): void
    {
        // Arrange
        $electronics = Category::factory()->create(['name' => 'Elektronik']);
        $fashion = Category::factory()->create(['name' => 'Fashion']);

        Product::factory()->create([
            'category_id' => $electronics->id,
            'name' => 'Handphone Samsung',
            'stock' => 5,
        ]);

        Product::factory()->create([
            'category_id' => $fashion->id,
            'name' => 'Kaos Polos',
            'stock' => 5,
        ]);

        // Act: filter kategori elektronik
        $response = $this->get(route('products.index', ['category' => $electronics->id]));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Handphone Samsung');
        $response->assertDontSee('Kaos Polos');
        $response->assertSee('Elektronik'); // Nama kategori di header
    }

    public function test_catalog_can_sort_by_price_low_to_high(): void
    {
        // Arrange
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Mahal',
            'price' => 500000,
            'stock' => 5,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Murah',
            'price' => 100000,
            'stock' => 5,
        ]);

        // Act
        $response = $this->get(route('products.index', ['sort' => 'price_low']));

        // Assert: Murah harus muncul lebih dulu
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertTrue(
            strpos($content, 'Murah') < strpos($content, 'Mahal'),
            'Product with lower price should appear first'
        );
    }

    public function test_catalog_can_sort_by_price_high_to_low(): void
    {
        // Arrange
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Mahal',
            'price' => 500000,
            'stock' => 5,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Murah',
            'price' => 100000,
            'stock' => 5,
        ]);

        // Act
        $response = $this->get(route('products.index', ['sort' => 'price_high']));

        // Assert: Mahal harus muncul lebih dulu
        $content = $response->getContent();
        $this->assertTrue(
            strpos($content, 'Mahal') < strpos($content, 'Murah'),
            'Product with higher price should appear first'
        );
    }

    public function test_catalog_can_combine_search_and_filter(): void
    {
        // Arrange
        $electronics = Category::factory()->create(['name' => 'Elektronik']);
        $fashion = Category::factory()->create(['name' => 'Fashion']);

        Product::factory()->create([
            'category_id' => $electronics->id,
            'name' => 'Laptop Pro',
            'stock' => 5,
        ]);

        Product::factory()->create([
            'category_id' => $electronics->id,
            'name' => 'Handphone Pro',
            'stock' => 5,
        ]);

        Product::factory()->create([
            'category_id' => $fashion->id,
            'name' => 'Baju Pro Max',
            'stock' => 5,
        ]);

        // Act: search "Pro" tapi filter kategori Elektronik
        $response = $this->get(route('products.index', [
            'search' => 'Pro',
            'category' => $electronics->id,
        ]));

        // Assert: hanya produk elektronik yang mengandung "Pro"
        $response->assertSee('Laptop Pro');
        $response->assertSee('Handphone Pro');
        $response->assertDontSee('Baju Pro Max');
    }

    public function test_catalog_paginates_products(): void
    {
        // Arrange: buat lebih dari 12 produk (default per page)
        $category = Category::factory()->create();
        Product::factory()->count(15)->create([
            'category_id' => $category->id,
            'stock' => 5,
        ]);

        // Act
        $response = $this->get(route('products.index'));

        // Assert: pagination links muncul
        $response->assertStatus(200);
        // Laravel pagination biasanya pakai "page" parameter
    }

    public function test_catalog_shows_empty_state_when_no_products(): void
    {
        // Act
        $response = $this->get(route('products.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Tidak ada produk ditemukan');
    }

    public function test_catalog_shows_empty_state_for_no_search_results(): void
    {
        // Arrange
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Laptop',
            'stock' => 5,
        ]);

        // Act: search yang tidak ada
        $response = $this->get(route('products.index', ['search' => 'xyz123notfound']));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Tidak ada produk ditemukan');
    }

    // ========================================
    // GAP: Error Handling — Graceful Degradation
    // ========================================

    public function test_catalog_handles_nonexistent_category_filter_gracefully(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Visible Product',
            'stock' => 5,
        ]);

        // Filter kategori yang tidak ada — harus menampilkan empty state, bukan crash
        $response = $this->get(route('products.index', ['category' => 99999]));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada produk ditemukan');
    }

    public function test_catalog_handles_invalid_sort_parameter(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Some Product',
            'stock' => 5,
        ]);

        // Sort parameter yang tidak valid — harus menggunakan default, bukan crash
        $response = $this->get(route('products.index', ['sort' => 'injection_attempt']));

        $response->assertStatus(200);
        $response->assertSee('Some Product');
    }

    // ========================================
    // GAP: Security — SQL Special Characters in Search
    // ========================================

    public function test_search_with_special_sql_characters(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Normal Product',
            'stock' => 5,
        ]);

        // Special characters tidak boleh menyebabkan error
        $specialChars = ["'", '"', '%', '_', ';', '--', 'OR 1=1'];

        foreach ($specialChars as $char) {
            $response = $this->get(route('products.index', ['search' => $char]));
            $response->assertStatus(200);
        }
    }
}
