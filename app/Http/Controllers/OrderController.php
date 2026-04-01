<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controller untuk menangani order history dari sisi User.
 *
 * Features:
 * - Daftar semua pesanan user (order history)
 * - Detail pesanan dengan status dan rincian item
 * - Batalkan pesanan (hanya jika masih pending)
 */
class OrderController extends Controller
{
    /**
     * Tampilkan daftar semua pesanan user (Order History).
     *
     * Diurutkan dari yang terbaru ke terlama.
     * Menampilkan status terkini setiap pesanan.
     */
    public function index(): View
    {
        $orders = auth()->user()
            ->orders()
            ->with('payment') // Eager load untuk menampilkan status pembayaran
            ->latest('order_date')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Tampilkan detail pesanan.
     *
     * Menampilkan:
     * - Info order (tanggal, status, alamat)
     * - Daftar item dengan harga snapshot (bukan harga produk saat ini)
     * - Info pembayaran jika sudah ada
     */
    public function show(Order $order): View|RedirectResponse
    {
        // Guard: pastikan order milik user yang login
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        // Load relasi yang dibutuhkan
        // items.product untuk nama produk (fallback jika produk dihapus)
        $order->load(['items.product', 'payment']);

        return view('orders.show', compact('order'));
    }

    /**
     * Batalkan pesanan.
     *
     * Hanya bisa dibatalkan jika:
     * - Order milik user yang login
     * - Status masih pending (belum bayar)
     *
     * Sesuai SRS: user tidak boleh cancel setelah status berubah ke paid/processing.
     */
    public function cancel(Order $order): RedirectResponse
    {
        // Guard: pastikan order milik user yang login
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        // Check apakah bisa dibatalkan menggunakan state machine
        if (! $order->canBeCancelled()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Pesanan tidak dapat dibatalkan karena sudah diproses.');
        }

        // Transisi status dan kembalikan stok dalam satu transaction
        // agar data tetap konsisten jika salah satu operasi gagal
        DB::transaction(function () use ($order) {
            $order->transitionTo(Order::STATUS_CANCELLED);

            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
        });

        return redirect()
            ->route('profile.show', ['tab' => 'transactions'])
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
