<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk menangani pembayaran dari sisi User.
 *
 * Flow:
 * 1. User memilih metode pembayaran (transfer/e-wallet/COD)
 * 2. Jika transfer/e-wallet: upload bukti bayar → status pending → tunggu verifikasi admin
 * 3. Jika COD: langsung masuk antrean admin dengan payment status pending
 */
class PaymentController extends Controller
{
    /**
     * Tampilkan halaman pemilihan metode pembayaran.
     *
     * Guard: pastikan order milik user yang login dan masih pending.
     */
    public function create(Order $order): View|RedirectResponse
    {
        // Guard: pastikan order milik user yang login
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        // Guard: pastikan order masih pending (belum bayar)
        if ($order->order_status !== Order::STATUS_PENDING) {
            return redirect()
                ->route('orders.show', $order)
                ->with('info', 'Pesanan ini sudah diproses.');
        }

        // Guard: pastikan belum ada payment record
        if ($order->payment !== null) {
            return redirect()
                ->route('orders.show', $order)
                ->with('info', 'Pembayaran sudah tercatat untuk pesanan ini.');
        }

        $order->load('items.product');

        return view('payments.create', [
            'order' => $order,
            'paymentMethods' => Payment::getMethodLabels(),
        ]);
    }

    /**
     * Proses dan simpan pembayaran.
     *
     * Validation:
     * - payment_method: required, harus salah satu dari method yang tersedia
     * - payment_proof: required jika bukan COD, format jpg/png, max 2MB
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        // Guard: pastikan order milik user yang login
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        // Guard: pastikan order masih pending
        if ($order->order_status !== Order::STATUS_PENDING) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Pesanan ini sudah diproses.');
        }

        // Guard: pastikan belum ada payment
        if ($order->payment !== null) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Pembayaran sudah tercatat.');
        }

        // Validasi input
        $validated = $request->validate([
            'payment_method' => ['required', 'in:' . implode(',', Payment::getMethods())],
            'payment_proof' => [
                // Conditional required: wajib jika bukan COD
                $request->payment_method !== Payment::METHOD_COD ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048', // Max 2MB
            ],
        ], [
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'payment_proof.required' => 'Bukti pembayaran wajib diunggah untuk metode ini.',
            'payment_proof.image' => 'File harus berupa gambar.',
            'payment_proof.mimes' => 'Format file harus JPG atau PNG.',
            'payment_proof.max' => 'Ukuran file maksimal 2MB.',
        ]);

        // Handle upload bukti bayar
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            // Simpan ke storage/app/public/payment_proofs
            // Filename: {order_id}_{timestamp}.{extension}
            $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        // Buat record payment
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $validated['payment_method'],
            'payment_status' => Payment::STATUS_PENDING,
            'payment_proof' => $proofPath,
            'payment_date' => now(),
        ]);

        // Redirect ke halaman detail order dengan pesan sukses
        $message = $validated['payment_method'] === Payment::METHOD_COD
            ? 'Pesanan berhasil dibuat dengan metode COD. Siapkan uang tunai saat pesanan diantar.'
            : 'Bukti pembayaran berhasil diunggah. Tunggu verifikasi dari admin.';

        return redirect()
            ->route('orders.show', $order)
            ->with('success', $message);
    }
}
