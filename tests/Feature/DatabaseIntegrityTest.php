<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite untuk Database Integrity.
 *
 * Covers:
 * - Cascade delete behavior saat user dihapus
 * - Foreign key constraint behavior
 * - One-to-one constraint enforcement (cart per user, payment per order)
 * - Data consistency pada edge cases
 */
class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // Cascade / Cleanup on User Deletion
    // ========================================

    public function test_deleting_user_cleans_up_cart_and_items(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 10,
        ]);

        // Buat cart dan items
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Verifikasi data ada sebelum delete
        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        $this->assertEquals(1, CartItem::count());

        // Delete user via profile endpoint (sama seperti user flow)
        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        // User sudah terhapus
        $this->assertNull(User::find($user->id));

        // Cart dan items juga harus terhapus (cascade atau cleanup)
        $this->assertDatabaseMissing('carts', ['user_id' => $user->id]);
        $this->assertEquals(0, CartItem::count());
    }

    // ========================================
    // One Cart Per User
    // ========================================

    public function test_only_one_cart_per_user_via_first_or_create(): void
    {
        $user = User::factory()->create();

        // firstOrCreate dipanggil dua kali harus tetap menghasilkan 1 cart
        $cart1 = Cart::firstOrCreate(['user_id' => $user->id]);
        $cart2 = Cart::firstOrCreate(['user_id' => $user->id]);

        $this->assertEquals($cart1->id, $cart2->id);
        $this->assertEquals(1, Cart::where('user_id', $user->id)->count());
    }

    // ========================================
    // One Payment Per Order (application level)
    // ========================================

    public function test_controller_prevents_duplicate_payment_per_order(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 10,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 100000,
            'order_date' => now(),
            'order_status' => Order::STATUS_PENDING,
            'address_shipment' => 'Jl. Test No. 1',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50000,
        ]);

        // Buat payment pertama
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => Payment::METHOD_COD,
            'payment_status' => Payment::STATUS_PENDING,
            'payment_date' => now(),
        ]);

        // Coba buat payment kedua via controller
        $this->actingAs($user);

        $response = $this->post(route('payment.store', $order), [
            'payment_method' => Payment::METHOD_TRANSFER,
        ]);

        // Harus ditolak
        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('error');

        // Tetap hanya 1 payment
        $this->assertEquals(1, Payment::where('order_id', $order->id)->count());
    }

    // ========================================
    // Cart Displays Correctly When Product Out of Stock
    // ========================================

    public function test_cart_displays_correctly_when_item_product_out_of_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Stok Habis Setelah Add',
            'price' => 50000,
            'stock' => 5,
        ]);

        // Tambah ke cart
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        // Stok berubah jadi 0 setelah ditambahkan ke cart (misalnya dibeli user lain)
        $product->update(['stock' => 0]);

        // Cart page harus tetap bisa dirender tanpa error
        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee('Stok Habis Setelah Add');
    }

    // ========================================
    // Order Detail When Product Deleted
    // ========================================

    public function test_order_detail_renders_when_product_deleted(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Produk yang akan dihapus',
            'price' => 50000,
            'stock' => 10,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 100000,
            'order_date' => now(),
            'order_status' => Order::STATUS_COMPLETED,
            'address_shipment' => 'Jl. Test',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50000,
        ]);

        // Hapus produk (simulasi admin menghapus produk)
        $product->delete();

        // Order detail harus tetap bisa dirender
        $this->actingAs($user);

        $response = $this->get(route('orders.show', $order));

        $response->assertOk();
        $response->assertSee('Detail Pesanan');
        $response->assertSee('Rp 100.000');
    }
}
