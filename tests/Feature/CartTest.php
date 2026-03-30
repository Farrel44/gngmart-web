<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->for($this->category)->create([
            'price' => 50000,
            'stock' => 10,
        ]);
    }

    // ========================================
    // Authentication Tests
    // ========================================

    public function test_guest_cannot_access_cart(): void
    {
        $response = $this->get(route('cart.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_add_to_cart(): void
    {
        $response = $this->post(route('cart.store'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('login'));
    }

    // ========================================
    // View Cart Tests
    // ========================================

    public function test_user_can_view_empty_cart(): void
    {
        $response = $this->actingAs($this->user)->get(route('cart.index'));

        $response->assertOk();
        $response->assertViewIs('cart.index');
    }

    public function test_user_can_view_cart_with_items(): void
    {
        // Setup: buat cart dengan item
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)->get(route('cart.index'));

        $response->assertOk();
        $response->assertViewHas('cart');
    }

    // ========================================
    // Add to Cart Tests
    // ========================================

    public function test_user_can_add_product_to_cart(): void
    {
        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        // Verifikasi item tersimpan
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_adding_same_product_increments_quantity(): void
    {
        // Tambah pertama kali
        $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        // Tambah lagi produk yang sama
        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $response->assertRedirect(route('cart.index'));

        // Verifikasi quantity bertambah (2 + 3 = 5)
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        // Pastikan hanya ada 1 cart item, bukan 2
        $this->assertEquals(1, CartItem::count());
    }

    public function test_cannot_add_more_than_stock(): void
    {
        // Produk hanya punya stok 10
        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $this->product->id,
            'quantity' => 15,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertEquals(0, CartItem::count());
    }

    public function test_cannot_add_if_total_exceeds_stock(): void
    {
        // Tambah 8 item pertama
        $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $this->product->id,
            'quantity' => 8,
        ]);

        // Coba tambah 5 lagi (total 13, padahal stok cuma 10)
        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        $response->assertSessionHasErrors('quantity');

        // Quantity tetap 8
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 8,
        ]);
    }

    public function test_cannot_add_nonexistent_product(): void
    {
        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => 99999,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    // ========================================
    // Update Quantity Tests
    // ========================================

    public function test_user_can_update_quantity(): void
    {
        // Setup: buat cart dengan item
        $cart = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)->patch(route('cart.update', $item), [
            'quantity' => 5,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        // Verifikasi quantity berubah
        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 5,
        ]);
    }

    public function test_cannot_update_quantity_exceeding_stock(): void
    {
        $cart = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)->patch(route('cart.update', $item), [
            'quantity' => 15,
        ]);

        $response->assertSessionHasErrors('quantity');

        // Quantity tetap 2
        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 2,
        ]);
    }

    // ========================================
    // Remove Item Tests
    // ========================================

    public function test_user_can_remove_item(): void
    {
        $cart = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)->delete(route('cart.destroy', $item));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_user_can_clear_cart(): void
    {
        $cart = Cart::create(['user_id' => $this->user->id]);

        // Tambah beberapa item
        $product2 = Product::factory()->for($this->category)->create();

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        $this->assertEquals(2, $cart->items()->count());

        $response = $this->actingAs($this->user)->delete(route('cart.clear'));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        // Semua items terhapus
        $this->assertEquals(0, $cart->fresh()->items()->count());
    }

    // ========================================
    // Authorization Tests
    // ========================================

    public function test_cannot_access_other_user_cart_item(): void
    {
        // User lain punya cart dengan item
        $otherUser = User::factory()->create();
        $otherCart = Cart::create(['user_id' => $otherUser->id]);
        $otherItem = CartItem::create([
            'cart_id' => $otherCart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        // User kita coba update item milik orang lain
        $response = $this->actingAs($this->user)->patch(route('cart.update', $otherItem), [
            'quantity' => 5,
        ]);

        $response->assertForbidden();

        // Quantity tetap 2
        $this->assertDatabaseHas('cart_items', [
            'id' => $otherItem->id,
            'quantity' => 2,
        ]);
    }

    public function test_cannot_delete_other_user_cart_item(): void
    {
        $otherUser = User::factory()->create();
        $otherCart = Cart::create(['user_id' => $otherUser->id]);
        $otherItem = CartItem::create([
            'cart_id' => $otherCart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)->delete(route('cart.destroy', $otherItem));

        $response->assertForbidden();

        // Item masih ada
        $this->assertDatabaseHas('cart_items', ['id' => $otherItem->id]);
    }

    // ========================================
    // Discount Price Tests
    // ========================================

    public function test_cart_uses_discount_price_for_subtotal(): void
    {
        // Produk dengan diskon
        $discountedProduct = Product::factory()->for($this->category)->create([
            'price' => 100000,
            'discount_price' => 75000,
            'stock' => 10,
        ]);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $discountedProduct->id,
            'quantity' => 2,
        ]);

        // Subtotal harus pakai discount_price (75000 × 2 = 150000)
        $this->assertEquals(150000, $item->getSubtotal());

        // Total cart juga harus benar
        $this->assertEquals(150000, $cart->getTotalPrice());
    }

    public function test_cart_uses_normal_price_when_no_discount(): void
    {
        // Produk tanpa diskon
        $normalProduct = Product::factory()->for($this->category)->create([
            'price' => 80000,
            'discount_price' => null,
            'stock' => 10,
        ]);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $normalProduct->id,
            'quantity' => 3,
        ]);

        // Subtotal pakai harga normal (80000 × 3 = 240000)
        $this->assertEquals(240000, $item->getSubtotal());
    }

    // ========================================
    // Cart Helper Methods Tests
    // ========================================

    public function test_cart_total_items_calculation(): void
    {
        $product2 = Product::factory()->for($this->category)->create();

        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 2,
        ]);

        // Load items relation
        $cart->load('items');

        // Total items = 3 + 2 = 5
        $this->assertEquals(5, $cart->getTotalItems());
    }

    public function test_cart_total_price_calculation(): void
    {
        $product2 = Product::factory()->for($this->category)->create([
            'price' => 30000,
            'discount_price' => null,
        ]);

        $cart = Cart::create(['user_id' => $this->user->id]);

        // Product 1: 50000 × 2 = 100000
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        // Product 2: 30000 × 3 = 90000
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);

        // Load items with products
        $cart->load('items.product');

        // Total = 100000 + 90000 = 190000
        $this->assertEquals(190000, $cart->getTotalPrice());
    }

    // ========================================
    // GAP: Guest Access for Update/Delete/Clear
    // ========================================

    public function test_guest_cannot_update_cart_item(): void
    {
        $cart = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->patch(route('cart.update', $item), ['quantity' => 5]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_delete_cart_item(): void
    {
        $cart = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->delete(route('cart.destroy', $item));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_clear_cart(): void
    {
        $response = $this->delete(route('cart.clear'));

        $response->assertRedirect(route('login'));
    }

    // ========================================
    // GAP: Validation Boundary Tests
    // ========================================

    public function test_cannot_add_to_cart_with_zero_quantity(): void
    {
        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $this->product->id,
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertEquals(0, CartItem::count());
    }

    public function test_cannot_add_to_cart_with_negative_quantity(): void
    {
        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $this->product->id,
            'quantity' => -3,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertEquals(0, CartItem::count());
    }

    public function test_cannot_update_cart_with_zero_quantity(): void
    {
        $cart = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)->patch(route('cart.update', $item), [
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors('quantity');

        // Quantity tetap 2
        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 2,
        ]);
    }

    public function test_cannot_add_to_cart_without_product_id(): void
    {
        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    public function test_cannot_add_to_cart_without_quantity(): void
    {
        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $this->product->id,
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_cannot_add_to_cart_with_non_integer_quantity(): void
    {
        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $this->product->id,
            'quantity' => 'abc',
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertEquals(0, CartItem::count());
    }

    // ========================================
    // GAP: Out of Stock Product
    // ========================================

    public function test_cannot_add_out_of_stock_product_to_cart(): void
    {
        $outOfStockProduct = Product::factory()->for($this->category)->create([
            'price' => 50000,
            'stock' => 0,
        ]);

        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $outOfStockProduct->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertEquals(0, CartItem::count());
    }

    // ========================================
    // GAP: Security — Mass Assignment
    // ========================================

    public function test_cart_store_ignores_extra_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('cart.store'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 1, // Extra field: harus diabaikan
            'cart_id' => 999, // Extra field: harus diabaikan
        ]);

        $response->assertRedirect(route('cart.index'));

        // Item tetap menggunakan harga produk asli, bukan injected price
        $item = CartItem::first();
        $this->assertNotNull($item);
        $this->assertEquals(1, $item->quantity);
        $this->assertEquals($this->product->getEffectivePrice(), $item->getSubtotal());
    }
}
