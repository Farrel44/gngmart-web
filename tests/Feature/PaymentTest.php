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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test suite untuk Payment feature (Phase 8.1).
 *
 * Covers:
 * - Payment page access dan guards
 * - Payment method selection
 * - Bukti bayar upload (transfer/ewallet)
 * - COD flow (tanpa bukti bayar)
 */
class PaymentTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // Setup Helper Methods
    // ========================================

    private function createUserWithPendingOrder(): array
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50000,
            'stock' => 10,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 100000,
            'order_date' => now(),
            'order_status' => Order::STATUS_PENDING,
            'address_shipment' => 'Jl. Test No. 123',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50000,
        ]);

        return compact('user', 'order', 'product');
    }

    // ========================================
    // Authentication & Guard Tests
    // ========================================

    public function test_guest_cannot_access_payment_page(): void
    {
        $data = $this->createUserWithPendingOrder();

        $response = $this->get(route('payment.create', $data['order']));

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_access_other_users_payment_page(): void
    {
        $data = $this->createUserWithPendingOrder();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser);

        $response = $this->get(route('payment.create', $data['order']));

        $response->assertForbidden();
    }

    public function test_user_cannot_pay_for_other_users_order(): void
    {
        Storage::fake('public');

        $data = $this->createUserWithPendingOrder();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser);

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_COD,
        ]);

        $response->assertForbidden();
    }

    // ========================================
    // Payment Page Display Tests
    // ========================================

    public function test_user_can_view_payment_page_for_pending_order(): void
    {
        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        $response = $this->get(route('payment.create', $data['order']));

        $response->assertOk();
        $response->assertViewIs('payments.create');
        $response->assertSee('Pilih Metode Pembayaran');
        $response->assertSee('Rp 100.000'); // Total
    }

    public function test_user_redirected_if_order_not_pending(): void
    {
        $data = $this->createUserWithPendingOrder();
        $data['order']->update(['order_status' => Order::STATUS_PAID]);

        $this->actingAs($data['user']);

        $response = $this->get(route('payment.create', $data['order']));

        $response->assertRedirect(route('orders.show', $data['order']));
        $response->assertSessionHas('info');
    }

    public function test_user_redirected_if_payment_already_exists(): void
    {
        $data = $this->createUserWithPendingOrder();

        // Create existing payment
        Payment::create([
            'order_id' => $data['order']->id,
            'payment_method' => Payment::METHOD_TRANSFER,
            'payment_status' => Payment::STATUS_PENDING,
            'payment_date' => now(),
        ]);

        $this->actingAs($data['user']);

        $response = $this->get(route('payment.create', $data['order']));

        $response->assertRedirect(route('orders.show', $data['order']));
    }

    // ========================================
    // COD Payment Tests
    // ========================================

    public function test_user_can_pay_with_cod(): void
    {
        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_COD,
        ]);

        $response->assertRedirect(route('orders.show', $data['order']));
        $response->assertSessionHas('success');

        // Verify payment record
        $this->assertDatabaseHas('payments', [
            'order_id' => $data['order']->id,
            'payment_method' => Payment::METHOD_COD,
            'payment_status' => Payment::STATUS_PENDING,
            'payment_proof' => null,
        ]);
    }

    public function test_cod_payment_does_not_require_proof(): void
    {
        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        // COD without payment_proof should succeed
        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_COD,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('orders.show', $data['order']));
    }

    // ========================================
    // Transfer Payment Tests
    // ========================================

    public function test_user_can_pay_with_transfer_and_upload_proof(): void
    {
        Storage::fake('public');

        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        $file = UploadedFile::fake()->image('bukti-transfer.jpg', 800, 600);

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_TRANSFER,
            'payment_proof' => $file,
        ]);

        $response->assertRedirect(route('orders.show', $data['order']));
        $response->assertSessionHas('success');

        // Verify payment record has proof
        $payment = Payment::where('order_id', $data['order']->id)->first();
        $this->assertNotNull($payment->payment_proof);
        $this->assertEquals(Payment::METHOD_TRANSFER, $payment->payment_method);

        // Verify file was stored
        Storage::disk('public')->assertExists($payment->payment_proof);
    }

    public function test_transfer_payment_requires_proof(): void
    {
        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_TRANSFER,
            // No payment_proof
        ]);

        $response->assertSessionHasErrors('payment_proof');
    }

    // ========================================
    // E-wallet Payment Tests
    // ========================================

    public function test_user_can_pay_with_ewallet_and_upload_proof(): void
    {
        Storage::fake('public');

        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        $file = UploadedFile::fake()->image('bukti-ewallet.png', 400, 800);

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_EWALLET,
            'payment_proof' => $file,
        ]);

        $response->assertRedirect(route('orders.show', $data['order']));

        $payment = Payment::where('order_id', $data['order']->id)->first();
        $this->assertEquals(Payment::METHOD_EWALLET, $payment->payment_method);
        $this->assertNotNull($payment->payment_proof);
    }

    // ========================================
    // Validation Tests
    // ========================================

    public function test_payment_method_is_required(): void
    {
        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        $response = $this->post(route('payment.store', $data['order']), []);

        $response->assertSessionHasErrors('payment_method');
    }

    public function test_invalid_payment_method_rejected(): void
    {
        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => 'invalid_method',
        ]);

        $response->assertSessionHasErrors('payment_method');
    }

    public function test_payment_proof_must_be_image(): void
    {
        Storage::fake('public');

        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_TRANSFER,
            'payment_proof' => $file,
        ]);

        $response->assertSessionHasErrors('payment_proof');
    }

    public function test_payment_proof_must_be_jpg_or_png(): void
    {
        Storage::fake('public');

        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        $file = UploadedFile::fake()->image('bukti.gif');

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_TRANSFER,
            'payment_proof' => $file,
        ]);

        $response->assertSessionHasErrors('payment_proof');
    }

    public function test_payment_proof_max_size_2mb(): void
    {
        Storage::fake('public');

        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        // Create file larger than 2MB
        $file = UploadedFile::fake()->image('bukti.jpg')->size(3000);

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_TRANSFER,
            'payment_proof' => $file,
        ]);

        $response->assertSessionHasErrors('payment_proof');
    }

    // ========================================
    // Double Payment Prevention Tests
    // ========================================

    public function test_cannot_create_payment_if_already_exists(): void
    {
        $data = $this->createUserWithPendingOrder();

        // Create existing payment
        Payment::create([
            'order_id' => $data['order']->id,
            'payment_method' => Payment::METHOD_COD,
            'payment_status' => Payment::STATUS_PENDING,
            'payment_date' => now(),
        ]);

        $this->actingAs($data['user']);

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_TRANSFER,
        ]);

        $response->assertRedirect(route('orders.show', $data['order']));
        $response->assertSessionHas('error');

        // Verify no duplicate payment
        $this->assertEquals(1, Payment::where('order_id', $data['order']->id)->count());
    }

    // ========================================
    // Payment Model Helper Tests
    // ========================================

    public function test_payment_requires_proof_method(): void
    {
        $transferPayment = new Payment(['payment_method' => Payment::METHOD_TRANSFER]);
        $ewalletPayment = new Payment(['payment_method' => Payment::METHOD_EWALLET]);
        $codPayment = new Payment(['payment_method' => Payment::METHOD_COD]);

        $this->assertTrue($transferPayment->requiresProof());
        $this->assertTrue($ewalletPayment->requiresProof());
        $this->assertFalse($codPayment->requiresProof());
    }

    public function test_payment_is_verified_method(): void
    {
        $pendingPayment = new Payment(['payment_status' => Payment::STATUS_PENDING]);
        $successPayment = new Payment(['payment_status' => Payment::STATUS_SUCCESS]);
        $failedPayment = new Payment(['payment_status' => Payment::STATUS_FAILED]);

        $this->assertFalse($pendingPayment->isVerified());
        $this->assertTrue($successPayment->isVerified());
        $this->assertFalse($failedPayment->isVerified());
    }

    // ========================================
    // GAP: Non-pending Order Payment Submission
    // ========================================

    public function test_cannot_submit_payment_for_non_pending_order(): void
    {
        $data = $this->createUserWithPendingOrder();

        // Ubah status order ke paid (tanpa payment record)
        $data['order']->update(['order_status' => Order::STATUS_PAID]);

        $this->actingAs($data['user']);

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_COD,
        ]);

        $response->assertRedirect(route('orders.show', $data['order']));
        $response->assertSessionHas('error');

        // Tidak ada payment dibuat
        $this->assertEquals(0, Payment::where('order_id', $data['order']->id)->count());
    }

    // ========================================
    // GAP: E-wallet Proof Required
    // ========================================

    public function test_ewallet_payment_requires_proof(): void
    {
        $data = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        // E-wallet tanpa bukti bayar harus gagal
        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_EWALLET,
            // No payment_proof
        ]);

        $response->assertSessionHasErrors('payment_proof');
    }

    // ========================================
    // GAP: Nonexistent Order
    // ========================================

    public function test_payment_for_nonexistent_order_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/orders/99999/payment');

        $response->assertNotFound();
    }

    // ========================================
    // GAP: Payment Model Helpers
    // ========================================

    public function test_payment_is_pending_method(): void
    {
        $pendingPayment = new Payment(['payment_status' => Payment::STATUS_PENDING]);
        $successPayment = new Payment(['payment_status' => Payment::STATUS_SUCCESS]);
        $failedPayment = new Payment(['payment_status' => Payment::STATUS_FAILED]);

        $this->assertTrue($pendingPayment->isPending());
        $this->assertFalse($successPayment->isPending());
        $this->assertFalse($failedPayment->isPending());
    }

    public function test_payment_method_label(): void
    {
        $transfer = new Payment(['payment_method' => Payment::METHOD_TRANSFER]);
        $ewallet = new Payment(['payment_method' => Payment::METHOD_EWALLET]);
        $cod = new Payment(['payment_method' => Payment::METHOD_COD]);

        $this->assertEquals('Transfer Bank', $transfer->getMethodLabel());
        $this->assertEquals('E-Wallet', $ewallet->getMethodLabel());
        $this->assertEquals('Bayar di Tempat (COD)', $cod->getMethodLabel());
    }

    public function test_payment_status_label(): void
    {
        $pending = new Payment(['payment_status' => Payment::STATUS_PENDING]);
        $success = new Payment(['payment_status' => Payment::STATUS_SUCCESS]);
        $failed = new Payment(['payment_status' => Payment::STATUS_FAILED]);

        $this->assertEquals('Menunggu Verifikasi', $pending->getStatusLabel());
        $this->assertEquals('Berhasil', $success->getStatusLabel());
        $this->assertEquals('Gagal', $failed->getStatusLabel());
    }

    // ========================================
    // GAP: Security — Mass Assignment
    // ========================================

    public function test_payment_ignores_extra_fields_payment_status_order_id(): void
    {
        $data = $this->createUserWithPendingOrder();
        $otherData = $this->createUserWithPendingOrder();

        $this->actingAs($data['user']);

        $response = $this->post(route('payment.store', $data['order']), [
            'payment_method' => Payment::METHOD_COD,
            'payment_status' => Payment::STATUS_SUCCESS, // Injeksi: langsung success
            'order_id' => $otherData['order']->id, // Injeksi: order lain
        ]);

        $response->assertRedirect(route('orders.show', $data['order']));

        // Payment harus tetap pending, bukan success
        $payment = Payment::where('order_id', $data['order']->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(Payment::STATUS_PENDING, $payment->payment_status);
        $this->assertEquals($data['order']->id, $payment->order_id);

        // Order lain tidak punya payment
        $this->assertEquals(0, Payment::where('order_id', $otherData['order']->id)->count());
    }
}
