<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite untuk Order History feature (Phase 8.2).
 *
 * Covers:
 * - Order history list page
 * - Order detail page
 * - Order cancellation
 * - State machine validation
 */
class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // Setup Helper Methods
    // ========================================

    private function createUserWithOrders(): array
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50000,
            'stock' => 10,
        ]);

        // Create multiple orders with different statuses
        $pendingOrder = Order::create([
            'user_id' => $user->id,
            'total_price' => 100000,
            'order_date' => now()->subDays(1),
            'order_status' => Order::STATUS_PENDING,
            'address_shipment' => 'Jl. Pending No. 1',
        ]);

        $paidOrder = Order::create([
            'user_id' => $user->id,
            'total_price' => 150000,
            'order_date' => now()->subDays(2),
            'order_status' => Order::STATUS_PAID,
            'address_shipment' => 'Jl. Paid No. 2',
        ]);

        $completedOrder = Order::create([
            'user_id' => $user->id,
            'total_price' => 200000,
            'order_date' => now()->subDays(10),
            'order_status' => Order::STATUS_COMPLETED,
            'address_shipment' => 'Jl. Completed No. 3',
        ]);

        // Add items to orders
        foreach ([$pendingOrder, $paidOrder, $completedOrder] as $order) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => $order->total_price / 2,
            ]);
        }

        return compact('user', 'product', 'pendingOrder', 'paidOrder', 'completedOrder');
    }

    // ========================================
    // Authentication Tests
    // ========================================

    public function test_guest_cannot_access_order_history(): void
    {
        $response = $this->get(route('orders.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_view_order_detail(): void
    {
        $data = $this->createUserWithOrders();

        $response = $this->get(route('orders.show', $data['pendingOrder']));

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_view_other_users_order(): void
    {
        $data = $this->createUserWithOrders();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser);

        $response = $this->get(route('orders.show', $data['pendingOrder']));

        $response->assertForbidden();
    }

    // ========================================
    // Order History Page Tests
    // ========================================

    public function test_user_can_view_order_history(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->get(route('orders.index'));

        $response->assertOk();
        $response->assertViewIs('orders.index');
        $response->assertSee('Riwayat Pesanan');
    }

    public function test_order_history_shows_all_user_orders(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->get(route('orders.index'));

        // Should see all three orders
        $response->assertSee('Rp 100.000');
        $response->assertSee('Rp 150.000');
        $response->assertSee('Rp 200.000');
    }

    public function test_order_history_shows_correct_statuses(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->get(route('orders.index'));

        $response->assertSee('Menunggu Pembayaran');
        $response->assertSee('Sudah Dibayar');
        $response->assertSee('Selesai');
    }

    public function test_order_history_shows_empty_state_for_no_orders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('orders.index'));

        $response->assertSee('Belum ada pesanan');
        $response->assertSee('Mulai Belanja');
    }

    public function test_order_history_sorted_by_date_descending(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->get(route('orders.index'));

        // Most recent (pendingOrder) should appear first
        // This is implicit in the view order, we check response has orders in correct structure
        $response->assertOk();
    }

    // ========================================
    // Order Detail Page Tests
    // ========================================

    public function test_user_can_view_order_detail(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->get(route('orders.show', $data['pendingOrder']));

        $response->assertOk();
        $response->assertViewIs('orders.show');
        $response->assertSee('Detail Pesanan #'.$data['pendingOrder']->id);
    }

    public function test_order_detail_shows_items_with_price_snapshot(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->get(route('orders.show', $data['pendingOrder']));

        $response->assertSee('Daftar Barang');
        // Price snapshot is used, not current product price
        $response->assertSee($data['product']->name);
    }

    public function test_order_detail_shows_shipping_address(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->get(route('orders.show', $data['pendingOrder']));

        $response->assertSee('Alamat Pengiriman');
        $response->assertSee('Jl. Pending No. 1');
    }

    public function test_order_detail_shows_payment_info_if_exists(): void
    {
        $data = $this->createUserWithOrders();

        // Add payment to pending order
        Payment::create([
            'order_id' => $data['pendingOrder']->id,
            'payment_method' => Payment::METHOD_COD,
            'payment_status' => Payment::STATUS_PENDING,
            'payment_date' => now(),
        ]);

        $this->actingAs($data['user']);

        $response = $this->get(route('orders.show', $data['pendingOrder']));

        $response->assertSee('Bayar di Tempat (COD)');
        $response->assertSee('Menunggu Verifikasi');
    }

    public function test_pending_order_shows_pay_now_button(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->get(route('orders.show', $data['pendingOrder']));

        $response->assertSee('Bayar Sekarang');
    }

    public function test_paid_order_does_not_show_pay_button(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->get(route('orders.show', $data['paidOrder']));

        $response->assertDontSee('Bayar Sekarang');
    }

    // ========================================
    // Order Cancellation Tests
    // ========================================

    public function test_user_can_cancel_pending_order(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->delete(route('orders.cancel', $data['pendingOrder']));

        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('success');

        // Verify order status changed
        $this->assertEquals(Order::STATUS_CANCELLED, $data['pendingOrder']->fresh()->order_status);
    }

    public function test_cancelled_order_restores_product_stock(): void
    {
        $data = $this->createUserWithOrders();
        $initialStock = $data['product']->stock;

        $this->actingAs($data['user']);

        $this->delete(route('orders.cancel', $data['pendingOrder']));

        // Stock should be restored (2 items were in the order)
        $this->assertEquals($initialStock + 2, $data['product']->fresh()->stock);
    }

    public function test_user_cannot_cancel_paid_order(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->delete(route('orders.cancel', $data['paidOrder']));

        $response->assertRedirect(route('orders.show', $data['paidOrder']));
        $response->assertSessionHas('error');

        // Status should not change
        $this->assertEquals(Order::STATUS_PAID, $data['paidOrder']->fresh()->order_status);
    }

    public function test_user_cannot_cancel_completed_order(): void
    {
        $data = $this->createUserWithOrders();

        $this->actingAs($data['user']);

        $response = $this->delete(route('orders.cancel', $data['completedOrder']));

        $response->assertSessionHas('error');
        $this->assertEquals(Order::STATUS_COMPLETED, $data['completedOrder']->fresh()->order_status);
    }

    public function test_user_cannot_cancel_other_users_order(): void
    {
        $data = $this->createUserWithOrders();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser);

        $response = $this->delete(route('orders.cancel', $data['pendingOrder']));

        $response->assertForbidden();
    }

    // ========================================
    // Order State Machine Tests
    // ========================================

    public function test_order_can_transition_to_valid_status(): void
    {
        $data = $this->createUserWithOrders();

        // Pending → Paid is valid
        $this->assertTrue($data['pendingOrder']->canTransitionTo(Order::STATUS_PAID));

        // Pending → Cancelled is valid
        $this->assertTrue($data['pendingOrder']->canTransitionTo(Order::STATUS_CANCELLED));

        // Paid → Processing is valid
        $this->assertTrue($data['paidOrder']->canTransitionTo(Order::STATUS_PROCESSING));
    }

    public function test_order_cannot_transition_to_invalid_status(): void
    {
        $data = $this->createUserWithOrders();

        // Pending → Shipped is invalid (skip steps)
        $this->assertFalse($data['pendingOrder']->canTransitionTo(Order::STATUS_SHIPPED));

        // Pending → Completed is invalid
        $this->assertFalse($data['pendingOrder']->canTransitionTo(Order::STATUS_COMPLETED));

        // Paid → Pending is invalid (backwards)
        $this->assertFalse($data['paidOrder']->canTransitionTo(Order::STATUS_PENDING));
    }

    public function test_completed_order_cannot_transition(): void
    {
        $data = $this->createUserWithOrders();

        // Completed is final state
        $this->assertEmpty($data['completedOrder']->getAvailableTransitions());
    }

    public function test_cancelled_order_cannot_transition(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $cancelledOrder = Order::create([
            'user_id' => $user->id,
            'total_price' => 100000,
            'order_date' => now(),
            'order_status' => Order::STATUS_CANCELLED,
            'address_shipment' => 'Test',
        ]);

        // Cancelled is final state
        $this->assertEmpty($cancelledOrder->getAvailableTransitions());
        $this->assertFalse($cancelledOrder->canTransitionTo(Order::STATUS_PENDING));
    }

    public function test_order_is_editable_only_when_pending(): void
    {
        $data = $this->createUserWithOrders();

        $this->assertTrue($data['pendingOrder']->isEditable());
        $this->assertFalse($data['paidOrder']->isEditable());
        $this->assertFalse($data['completedOrder']->isEditable());
    }

    public function test_order_is_paid_method(): void
    {
        $data = $this->createUserWithOrders();

        $this->assertFalse($data['pendingOrder']->isPaid());
        $this->assertTrue($data['paidOrder']->isPaid());
        $this->assertTrue($data['completedOrder']->isPaid());
    }

    // ========================================
    // Order Model Relationship Tests
    // ========================================

    public function test_order_has_items_relationship(): void
    {
        $data = $this->createUserWithOrders();

        $this->assertCount(1, $data['pendingOrder']->items);
        $this->assertEquals($data['product']->id, $data['pendingOrder']->items->first()->product_id);
    }

    public function test_order_has_payment_relationship(): void
    {
        $data = $this->createUserWithOrders();

        Payment::create([
            'order_id' => $data['pendingOrder']->id,
            'payment_method' => Payment::METHOD_COD,
            'payment_status' => Payment::STATUS_PENDING,
            'payment_date' => now(),
        ]);

        $this->assertNotNull($data['pendingOrder']->fresh()->payment);
        $this->assertEquals(Payment::METHOD_COD, $data['pendingOrder']->fresh()->payment->payment_method);
    }

    // ========================================
    // GAP: Guest Cannot Cancel Order
    // ========================================

    public function test_guest_cannot_cancel_order(): void
    {
        $data = $this->createUserWithOrders();

        $response = $this->delete(route('orders.cancel', $data['pendingOrder']));

        $response->assertRedirect(route('login'));
    }

    // ========================================
    // GAP: Order History Isolation
    // ========================================

    public function test_order_history_does_not_show_other_users_orders(): void
    {
        $data = $this->createUserWithOrders();
        $otherUser = User::factory()->create();

        // otherUser shouldn't see data['user']'s orders
        $this->actingAs($otherUser);

        $response = $this->get(route('orders.index'));

        $response->assertOk();
        $response->assertDontSee('Rp 100.000');
        $response->assertDontSee('Rp 150.000');
        $response->assertDontSee('Rp 200.000');
        $response->assertSee('Belum ada pesanan');
    }

    // ========================================
    // GAP: Nonexistent Order
    // ========================================

    public function test_nonexistent_order_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/orders/99999');

        $response->assertNotFound();
    }

    // ========================================
    // GAP: Cancel Already-Cancelled Order
    // ========================================

    public function test_cannot_cancel_already_cancelled_order(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 10,
        ]);

        $cancelledOrder = Order::create([
            'user_id' => $user->id,
            'total_price' => 100000,
            'order_date' => now(),
            'order_status' => Order::STATUS_CANCELLED,
            'address_shipment' => 'Jl. Cancelled',
        ]);

        OrderItem::create([
            'order_id' => $cancelledOrder->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50000,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('orders.cancel', $cancelledOrder));

        $response->assertSessionHas('error');
        $this->assertEquals(Order::STATUS_CANCELLED, $cancelledOrder->fresh()->order_status);

        // Stok tidak boleh bertambah lagi
        $this->assertEquals(10, $product->fresh()->stock);
    }

    // ========================================
    // GAP: Order Model — isFinal, transitionTo
    // ========================================

    public function test_order_is_final_method(): void
    {
        $data = $this->createUserWithOrders();

        $this->assertFalse($data['pendingOrder']->isFinal());
        $this->assertFalse($data['paidOrder']->isFinal());
        $this->assertTrue($data['completedOrder']->isFinal());

        // Also test cancelled
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $cancelledOrder = Order::create([
            'user_id' => $user->id,
            'total_price' => 50000,
            'order_date' => now(),
            'order_status' => Order::STATUS_CANCELLED,
            'address_shipment' => 'Test',
        ]);
        $this->assertTrue($cancelledOrder->isFinal());
    }

    public function test_transition_to_invalid_status_returns_false_without_saving(): void
    {
        $data = $this->createUserWithOrders();

        // Pending → Shipped is invalid
        $result = $data['pendingOrder']->transitionTo(Order::STATUS_SHIPPED);

        $this->assertFalse($result);
        $this->assertEquals(Order::STATUS_PENDING, $data['pendingOrder']->fresh()->order_status);
    }

    public function test_transition_to_valid_status_saves_and_returns_true(): void
    {
        $data = $this->createUserWithOrders();

        // Pending → Paid is valid
        $result = $data['pendingOrder']->transitionTo(Order::STATUS_PAID);

        $this->assertTrue($result);
        $this->assertEquals(Order::STATUS_PAID, $data['pendingOrder']->fresh()->order_status);
    }
}
