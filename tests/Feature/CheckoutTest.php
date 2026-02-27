<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'address' => 'Jl. Contoh No. 123, RT 01/RW 02, Kelurahan Test, Kecamatan Sample, Kota Demo 12345',
        ]);
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->for($this->category)->create([
            'name' => 'Test Product',
            'price' => 100000,
            'discount_price' => null,
            'stock' => 10,
        ]);
    }

    // ========================================
    // Authentication & Guard Tests
    // ========================================

    public function test_guest_cannot_access_checkout(): void
    {
        $response = $this->get(route('checkout.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_submit_checkout(): void
    {
        $response = $this->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Test Alamat',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_checkout_with_empty_cart(): void
    {
        $response = $this->actingAs($this->user)->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_user_cannot_submit_checkout_with_empty_cart(): void
    {
        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Test Alamat Lengkap Sekali',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    // ========================================
    // Checkout Page Tests
    // ========================================

    public function test_user_can_view_checkout_page(): void
    {
        $this->setupCartWithItem();

        $response = $this->actingAs($this->user)->get(route('checkout.index'));

        $response->assertOk();
        $response->assertViewIs('checkout.index');
        $response->assertViewHas('cart');
        $response->assertViewHas('user');
    }

    public function test_checkout_page_shows_cart_items(): void
    {
        $this->setupCartWithItem(quantity: 3);

        $response = $this->actingAs($this->user)->get(route('checkout.index'));

        $response->assertOk();
        $response->assertSee($this->product->name);
        $response->assertSee('Rp 300.000'); // 100.000 × 3
    }

    public function test_checkout_page_autofills_user_address(): void
    {
        $this->setupCartWithItem();

        $response = $this->actingAs($this->user)->get(route('checkout.index'));

        $response->assertOk();
        $response->assertSee($this->user->address);
    }

    // ========================================
    // Place Order Tests (Happy Path)
    // ========================================

    public function test_user_can_place_order(): void
    {
        $this->setupCartWithItem(quantity: 2);
        $initialStock = $this->product->stock;

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);

        // Redirect ke halaman payment
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Order dibuat dengan benar
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'order_status' => Order::STATUS_PENDING,
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);
    }

    public function test_order_has_correct_total_price(): void
    {
        $this->setupCartWithItem(quantity: 3);

        $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);

        // Total: 100000 × 3 = 300000
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals(300000, $order->total_price);
    }

    public function test_order_items_are_created_with_price_snapshot(): void
    {
        $this->setupCartWithItem(quantity: 2);

        $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);

        // Order item dibuat dengan harga tersimpan (snapshot)
        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 100000, // Harga snapshot, bukan reference
        ]);
    }

    public function test_order_uses_discount_price_when_available(): void
    {
        // Produk dengan diskon
        $discountProduct = Product::factory()->for($this->category)->create([
            'price' => 200000,
            'discount_price' => 150000,
            'stock' => 10,
        ]);

        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $discountProduct->id,
            'quantity' => 2,
        ]);

        $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);

        // Order item pakai discount price
        $this->assertDatabaseHas('order_items', [
            'product_id' => $discountProduct->id,
            'price' => 150000, // Discount price, bukan harga normal
        ]);

        // Total order: 150000 × 2 = 300000 (bukan 400000)
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals(300000, $order->total_price);
    }

    // ========================================
    // Stock Management Tests
    // ========================================

    public function test_stock_is_reduced_after_checkout(): void
    {
        $initialStock = $this->product->stock; // 10
        $this->setupCartWithItem(quantity: 3);

        $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);

        // Stok berkurang
        $this->product->refresh();
        $this->assertEquals($initialStock - 3, $this->product->stock); // 10 - 3 = 7
    }

    public function test_cannot_checkout_if_stock_insufficient(): void
    {
        // Cart minta 15, tapi stok cuma 10
        $this->setupCartWithItem(quantity: 15);

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);

        $response->assertSessionHasErrors('stock');

        // Order tidak dibuat
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->id,
        ]);

        // Stok tidak berubah
        $this->product->refresh();
        $this->assertEquals(10, $this->product->stock);
    }

    public function test_stock_validation_rechecks_at_checkout_time(): void
    {
        // Setup cart dengan quantity valid
        $this->setupCartWithItem(quantity: 5);

        // Simulate: stok berubah sebelum checkout (race condition scenario)
        $this->product->update(['stock' => 3]);

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);

        $response->assertSessionHasErrors('stock');

        // Order tidak dibuat karena stok sudah kurang
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->id,
        ]);
    }

    // ========================================
    // Cart Clearing Tests
    // ========================================

    public function test_cart_is_cleared_after_successful_checkout(): void
    {
        $this->setupCartWithItem(quantity: 2);

        // Pastikan cart punya item
        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertEquals(1, $cart->items()->count());

        $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);

        // Cart dikosongkan setelah checkout
        $cart->refresh();
        $this->assertEquals(0, $cart->items()->count());
    }

    public function test_cart_not_cleared_if_checkout_fails(): void
    {
        $this->setupCartWithItem(quantity: 20); // Lebih dari stok

        $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);

        // Cart masih ada karena checkout gagal
        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertEquals(1, $cart->items()->count());
    }

    // ========================================
    // Validation Tests
    // ========================================

    public function test_address_shipment_is_required(): void
    {
        $this->setupCartWithItem();

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => '',
        ]);

        $response->assertSessionHasErrors('address_shipment');
    }

    public function test_address_shipment_minimum_length(): void
    {
        $this->setupCartWithItem();

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Pendek', // < 10 chars
        ]);

        $response->assertSessionHasErrors('address_shipment');
    }

    // ========================================
    // Multiple Items Tests
    // ========================================

    public function test_checkout_with_multiple_items(): void
    {
        $product2 = Product::factory()->for($this->category)->create([
            'price' => 50000,
            'stock' => 20,
        ]);

        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);

        $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);

        // Total: (100000 × 2) + (50000 × 3) = 200000 + 150000 = 350000
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals(350000, $order->total_price);

        // 2 order items dibuat
        $this->assertEquals(2, $order->items()->count());

        // Kedua stok berkurang
        $this->product->refresh();
        $product2->refresh();
        $this->assertEquals(8, $this->product->stock); // 10 - 2
        $this->assertEquals(17, $product2->stock); // 20 - 3
    }

    // ========================================
    // Order Model Helper Tests
    // ========================================

    public function test_order_status_label(): void
    {
        $this->setupCartWithItem();

        $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman Test',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals('Menunggu Pembayaran', $order->getStatusLabel());
    }

    public function test_order_can_be_cancelled_when_pending(): void
    {
        $this->setupCartWithItem();

        $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman Test',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertTrue($order->canBeCancelled());
    }

    public function test_order_cannot_be_cancelled_after_paid(): void
    {
        $this->setupCartWithItem();

        $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman Test',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $order->update(['order_status' => Order::STATUS_PAID]);

        $this->assertFalse($order->canBeCancelled());
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Setup cart dengan item untuk testing.
     */
    private function setupCartWithItem(int $quantity = 1): Cart
    {
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => $quantity,
        ]);

        return $cart;
    }

    // ========================================
    // GAP: Validation Boundary Tests
    // ========================================

    public function test_address_shipment_maximum_length(): void
    {
        $this->setupCartWithItem();

        // Lebih dari 500 karakter harus ditolak
        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => str_repeat('A', 501),
        ]);

        $response->assertSessionHasErrors('address_shipment');
    }

    // ========================================
    // GAP: Multi-item Stock Failure
    // ========================================

    public function test_checkout_fails_when_one_item_out_of_stock_in_multi_item_cart(): void
    {
        // Produk kedua dengan stok sangat terbatas
        $product2 = Product::factory()->for($this->category)->create([
            'price' => 50000,
            'stock' => 1,
        ]);

        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2, // OK: stok 10
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 5, // FAIL: stok cuma 1
        ]);

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
        ]);

        $response->assertSessionHasErrors('stock');

        // Tidak ada order yang dibuat
        $this->assertDatabaseMissing('orders', ['user_id' => $this->user->id]);

        // Stok kedua produk tidak berubah (rollback transaction)
        $this->assertEquals(10, $this->product->fresh()->stock);
        $this->assertEquals(1, $product2->fresh()->stock);
    }

    // ========================================
    // GAP: Security — Mass Assignment
    // ========================================

    public function test_checkout_ignores_extra_fields_total_price_user_id_status(): void
    {
        $this->setupCartWithItem(quantity: 2);

        $otherUser = User::factory()->create();

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'address_shipment' => 'Jl. Pengiriman No. 456, Kota Tujuan',
            'total_price' => 1, // Injeksi: harga dipalsukan
            'user_id' => $otherUser->id, // Injeksi: user lain
            'order_status' => 'completed', // Injeksi: langsung selesai
        ]);

        $response->assertRedirect();

        // Order harus milik user yang login, bukan injeksi
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(200000, $order->total_price); // 100000 × 2
        $this->assertEquals(Order::STATUS_PENDING, $order->order_status);

        // Tidak ada order atas nama otherUser
        $this->assertDatabaseMissing('orders', ['user_id' => $otherUser->id]);
    }
}
