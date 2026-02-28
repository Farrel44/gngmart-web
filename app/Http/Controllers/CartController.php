<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Tampilkan halaman cart beserta semua items.
     */
    public function index(): View
    {
        $cart = $this->getOrCreateCart();

        // Eager load products untuk menghindari N+1 query
        $cart->load('items.product');

        return view('cart.index', compact('cart'));
    }

    /**
     * Tambah produk ke cart.
     * Jika produk sudah ada, increment quantity.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Validasi stok tersedia
        if ($product->stock < $validated['quantity']) {
            return back()->withErrors([
                'quantity' => 'Stok tidak mencukupi. Tersedia: ' . $product->stock,
            ]);
        }

        $cart = $this->getOrCreateCart();

        // Cek apakah produk sudah ada di cart
        $existingItem = $cart->items()->where('product_id', $product->id)->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $validated['quantity'];

            // Validasi total quantity tidak melebihi stok
            if ($newQuantity > $product->stock) {
                return back()->withErrors([
                    'quantity' => 'Total quantity melebihi stok. Tersedia: ' . $product->stock . ', di cart: ' . $existingItem->quantity,
                ]);
            }

            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
            ]);
        }

        // "Beli Sekarang" flow: redirect ke checkout setelah add to cart
        if ($request->input('redirect') === 'checkout') {
            return redirect()->route('checkout.index')
                ->with('success', 'Produk berhasil ditambahkan ke keranjang.');
        }

        return redirect()->route('cart.index')
            ->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * Update quantity item di cart.
     */
    public function update(Request $request, CartItem $item): RedirectResponse
    {
        // Pastikan item milik cart user yang sedang login
        $this->authorizeCartItem($item);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Validasi stok tersedia
        if ($item->product->stock < $validated['quantity']) {
            return back()->withErrors([
                'quantity' => 'Stok tidak mencukupi. Tersedia: ' . $item->product->stock,
            ]);
        }

        $item->update(['quantity' => $validated['quantity']]);

        return redirect()->route('cart.index')
            ->with('success', 'Quantity berhasil diupdate.');
    }

    /**
     * Hapus satu item dari cart.
     */
    public function destroy(CartItem $item): RedirectResponse
    {
        $this->authorizeCartItem($item);

        $item->delete();

        return redirect()->route('cart.index')
            ->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    /**
     * Kosongkan semua items di cart.
     */
    public function clear(): RedirectResponse
    {
        $cart = Auth::user()->cart;

        if ($cart) {
            $cart->clear();
        }

        return redirect()->route('cart.index')
            ->with('success', 'Keranjang berhasil dikosongkan.');
    }

    /**
     * Get cart milik user, atau buat baru jika belum ada.
     * Auth only: setiap user punya satu cart permanent.
     */
    private function getOrCreateCart(): Cart
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // firstOrCreate untuk memastikan hanya ada 1 cart per user
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Pastikan cart item milik user yang sedang login.
     * Abort 403 jika bukan miliknya.
     */
    private function authorizeCartItem(CartItem $item): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Item harus punya cart, dan cart harus milik user ini
        if (!$item->cart || $item->cart->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
    }
}
