<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk Product Detail (ProductController@show)
 * 
 * Memastikan halaman detail produk menampilkan informasi dengan benar,
 * termasuk SEO meta tags dan related products.
 */
class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_detail_page_can_be_rendered(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
        ]);

        // Act
        $response = $this->get(route('products.show', $product));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Test Product');
    }

    public function test_product_detail_uses_slug_in_url(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Laptop Gaming',
            'slug' => 'laptop-gaming',
        ]);

        // Act: akses via slug
        $response = $this->get('/products/laptop-gaming');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Laptop Gaming');
    }

    public function test_product_detail_shows_product_information(): void
    {
        // Arrange
        $category = Category::factory()->create(['name' => 'Elektronik']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Smartphone X',
            'description' => 'Deskripsi smartphone yang sangat bagus',
            'price' => 5000000,
            'stock' => 25,
        ]);

        // Act
        $response = $this->get(route('products.show', $product));

        // Assert: semua info produk muncul
        $response->assertSee('Smartphone X');
        $response->assertSee('Elektronik');
        $response->assertSee('5.000.000'); // formatted price
        $response->assertSee('Deskripsi smartphone yang sangat bagus');
        $response->assertSee('Stok Tersedia'); // stock > 10
    }

    public function test_product_detail_shows_limited_stock_warning(): void
    {
        // Arrange: stok antara 1-5
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 3,
        ]);

        // Act
        $response = $this->get(route('products.show', $product));

        // Assert
        $response->assertSee('Stok Terbatas');
        $response->assertSee('3 tersisa');
    }

    public function test_product_detail_shows_out_of_stock_status(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 0,
        ]);

        // Act
        $response = $this->get(route('products.show', $product));

        // Assert
        $response->assertSee('Stok Habis');
    }

    public function test_product_detail_has_seo_meta_tags(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'SEO Product',
            'meta_title' => 'Custom Meta Title',
            'meta_description' => 'Custom meta description for SEO',
            'meta_keywords' => 'keyword1, keyword2, keyword3',
        ]);

        // Act
        $response = $this->get(route('products.show', $product));

        // Assert: meta tags ada di head
        $response->assertSee('<title>Custom Meta Title | GNGMart</title>', false);
        $response->assertSee('content="Custom meta description for SEO"', false);
        $response->assertSee('content="keyword1, keyword2, keyword3"', false);
    }

    public function test_product_detail_has_open_graph_tags(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'OG Product',
            'description' => 'Product description',
        ]);

        // Act
        $response = $this->get(route('products.show', $product));

        // Assert: Open Graph tags
        $response->assertSee('og:title', false);
        $response->assertSee('og:type" content="product"', false);
    }

    public function test_product_detail_shows_related_products(): void
    {
        // Arrange
        $category = Category::factory()->create();
        
        $mainProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Main Product',
            'stock' => 10,
        ]);
        
        // Related product (same category)
        $relatedProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Related Product',
            'stock' => 10,
        ]);

        // Act
        $response = $this->get(route('products.show', $mainProduct));

        // Assert: section "Produk Terkait" muncul dengan related product
        $response->assertSee('Produk Terkait');
        $response->assertSee('Related Product');
    }

    public function test_related_products_only_from_same_category(): void
    {
        // Arrange: buat produk dari kategori berbeda
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        $mainProduct = Product::factory()->create([
            'category_id' => $category1->id,
            'stock' => 10,
        ]);
        
        // Related product (same category)
        $relatedProduct = Product::factory()->create([
            'category_id' => $category1->id,
            'stock' => 10,
        ]);
        
        // Different category (should not appear in related)
        $differentProduct = Product::factory()->create([
            'category_id' => $category2->id,
            'stock' => 10,
        ]);

        // Act
        $response = $this->get(route('products.show', $mainProduct));

        // Assert: verify query logic dengan memeriksa relatedProducts dari ViewData
        // Karena response bisa contain debug output, kita test viewData langsung
        $viewData = $response->viewData('relatedProducts');
        
        // Related products harus berisi produk dari kategori sama
        $this->assertTrue($viewData->contains('id', $relatedProduct->id));
        
        // Related products TIDAK boleh berisi produk dari kategori berbeda
        $this->assertFalse($viewData->contains('id', $differentProduct->id));
    }

    public function test_product_detail_does_not_show_main_product_in_related(): void
    {
        // Arrange
        $category = Category::factory()->create();
        
        $mainProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Unique Main Product Name',
            'stock' => 10,
        ]);
        
        $relatedProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Related Product',
            'stock' => 10,
        ]);

        // Act
        $response = $this->get(route('products.show', $mainProduct));

        // Assert: produk utama TIDAK boleh muncul di relatedProducts collection
        // Test viewData langsung untuk akurasi (menghindari false positive dari meta tags/title)
        $viewData = $response->viewData('relatedProducts');
        
        // Main product tidak boleh ada di related products
        $this->assertFalse(
            $viewData->contains('id', $mainProduct->id),
            'Main product should not appear in related products'
        );
        
        // Related product harus ada di related products
        $this->assertTrue(
            $viewData->contains('id', $relatedProduct->id),
            'Related product should appear in related products'
        );
    }

    public function test_product_detail_shows_breadcrumb_navigation(): void
    {
        // Arrange
        $category = Category::factory()->create(['name' => 'Gadget']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Smartwatch',
        ]);

        // Act
        $response = $this->get(route('products.show', $product));

        // Assert: breadcrumb links
        $response->assertSee('Beranda');
        $response->assertSee('Katalog');
        $response->assertSee('Gadget');
        $response->assertSee('Smartwatch');
    }

    public function test_nonexistent_product_returns_404(): void
    {
        // Act: akses produk yang tidak ada
        $response = $this->get('/products/nonexistent-product-slug');

        // Assert
        $response->assertStatus(404);
    }
}
