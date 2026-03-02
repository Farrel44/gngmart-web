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
     * Hanya item yang di-check di cart page yang masuk ke checkout.
     * Query param ?items=1,2,3 menentukan cart item IDs yang dipilih.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $cart = $this->getUserCartWithItems();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang kosong. Silakan tambahkan produk terlebih dahulu.');
        }

        // Filter hanya item yang dipilih user di halaman cart.
        // Tanpa ?items param → tampilkan semua (backward compat untuk "Beli Sekarang" flow).
        $items = $cart->items;
        $selectedIds = [];

        if ($request->has('items')) {
            $selectedIds = array_filter(
                array_map('intval', explode(',', $request->query('items', '')))
            );

            $items = $cart->items->whereIn('id', $selectedIds)->values();
        }

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Tidak ada item yang dipilih. Silakan pilih item untuk checkout.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        return view('checkout.index', [
            'cart' => $cart,
            'items' => $items,
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
            'selected_items' => ['required', 'array', 'min:1'],
            'selected_items.*' => ['integer'],
        ], [
            'address_shipment.required' => 'Alamat pengiriman wajib diisi.',
            'address_shipment.min' => 'Alamat pengiriman minimal 10 karakter.',
            'address_shipment.max' => 'Alamat pengiriman maksimal 500 karakter.',
            'selected_items.required' => 'Tidak ada item yang dipilih.',
        ]);

        $cart = $this->getUserCartWithItems();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang kosong. Tidak bisa checkout.');
        }

        // Hanya proses item yang memang dipilih user — item lain tetap di cart
        $selectedItems = $cart->items->whereIn('id', $validated['selected_items'])->values();

        if ($selectedItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Item yang dipilih tidak ditemukan di keranjang.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $order = DB::transaction(function () use ($cart, $selectedItems, $user, $validated) {
                // 1. Validasi stok hanya untuk item yang dipilih
                $this->validateStock($selectedItems);

                // 2. Hitung total harga dari selected items saja
                $totalPrice = $selectedItems->sum(fn($item) => $item->getSubtotal());

                // 3. Buat order baru
                $order = Order::create([
                    'user_id' => $user->id,
                    'total_price' => $totalPrice,
                    'order_date' => now(),
                    'order_status' => Order::STATUS_PENDING,
                    'address_shipment' => $validated['address_shipment'],
                ]);

                // 4. Buat order items dan kurangi stok
                foreach ($selectedItems as $cartItem) {
                    $order->items()->create([
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->product->getEffectivePrice(),
                    ]);

                    $cartItem->product->decrement('stock', $cartItem->quantity);
                }

                // 5. Hapus hanya item yang diproses — sisanya tetap di cart
                $cart->items()->whereIn('id', $selectedItems->pluck('id'))->delete();

                return $order;
            });

            return redirect()->route('payment.create', $order)
                ->with('success', 'Pesanan berhasil dibuat! Silakan pilih metode pembayaran.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
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
            ->with('items.product.images')
            ->first();
    }

    /**
     * Validasi stok item yang akan di-checkout.
     * Menerima Collection of CartItem (bukan Cart).
     * Fresh query per-item untuk menghindari race condition.
     */
    private function validateStock($items): void
    {
        $insufficientItems = [];

        foreach ($items as $item) {
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
