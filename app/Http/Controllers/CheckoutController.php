<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Tampilkan halaman checkout dengan ringkasan cart dan form alamat.
     * Autofill alamat dari profil user untuk kemudahan.
     */
    public function index(): View|RedirectResponse
    {
        $cart = $this->getUserCartWithItems();

        // Guard: jangan bisa checkout kalau cart kosong
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang kosong. Silakan tambahkan produk terlebih dahulu.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        return view('checkout.index', [
            'cart' => $cart,
            'user' => $user,
        ]);
    }

    /**
     * Proses checkout dan buat order baru.
     * 
     * Flow dalam DB::transaction untuk menjaga integritas data:
     * 1. Validasi stok semua item
     * 2. Buat order dengan status pending
     * 3. Buat order items (snapshot harga)
     * 4. Kurangi stok produk
     * 5. Kosongkan cart
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address_shipment' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'address_shipment.required' => 'Alamat pengiriman wajib diisi.',
            'address_shipment.min' => 'Alamat pengiriman minimal 10 karakter.',
            'address_shipment.max' => 'Alamat pengiriman maksimal 500 karakter.',
        ]);

        $cart = $this->getUserCartWithItems();

        // Guard: pastikan cart ada dan tidak kosong
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang kosong. Tidak bisa checkout.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $order = DB::transaction(function () use ($cart, $user, $validated) {
                // 1. Validasi stok semua item sebelum proses
                $this->validateStock($cart);

                // 2. Hitung total harga (pakai effective price)
                $totalPrice = $cart->getTotalPrice();

                // 3. Buat order baru
                $order = Order::create([
                    'user_id' => $user->id,
                    'total_price' => $totalPrice,
                    'order_date' => now(),
                    'order_status' => Order::STATUS_PENDING,
                    'address_shipment' => $validated['address_shipment'],
                ]);

                // 4. Buat order items dan kurangi stok (dalam satu loop untuk efisiensi)
                foreach ($cart->items as $cartItem) {
                    // Snapshot harga saat ini ke order item
                    $order->items()->create([
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->product->getEffectivePrice(),
                    ]);

                    // Kurangi stok produk
                    $cartItem->product->decrement('stock', $cartItem->quantity);
                }

                // 5. Kosongkan cart setelah order berhasil
                $cart->clear();

                return $order;
            });

            // Redirect ke halaman pilih metode pembayaran
            return redirect()->route('payment.create', $order)
                ->with('success', 'Pesanan berhasil dibuat! Silakan pilih metode pembayaran.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exception (stok tidak cukup)
            throw $e;
        } catch (\Exception $e) {
            // Log error untuk debugging, tampilkan pesan umum ke user
            report($e);

            return redirect()->route('checkout.index')
                ->with('error', 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.');
        }
    }

    /**
     * Ambil cart user beserta items dan products (eager loaded).
     */
    private function getUserCartWithItems(): ?Cart
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return Cart::where('user_id', $user->id)
            ->with('items.product')
            ->first();
    }

    /**
     * Validasi stok semua item di cart.
     * Throw ValidationException jika ada item dengan stok tidak cukup.
     * 
     * Menggunakan fresh query untuk menghindari race condition:
     * stok bisa berubah antara halaman checkout dan submit.
     */
    private function validateStock(Cart $cart): void
    {
        $insufficientItems = [];

        foreach ($cart->items as $item) {
            // Fresh query untuk dapat stok terbaru (hindari race condition)
            $currentStock = Product::where('id', $item->product_id)->value('stock');

            if ($currentStock < $item->quantity) {
                $insufficientItems[] = sprintf(
                    '%s (tersedia: %d, diminta: %d)',
                    $item->product->name,
                    $currentStock,
                    $item->quantity
                );
            }
        }

        if (!empty($insufficientItems)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'stock' => 'Stok tidak mencukupi untuk: ' . implode(', ', $insufficientItems),
            ]);
        }
    }
}
