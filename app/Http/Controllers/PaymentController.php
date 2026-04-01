<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

/**
 * Controller untuk menangani pembayaran dari sisi User.
 *
 * Flow:
 * 1. User memilih metode pembayaran (COD / Midtrans BCA)
 * 2. Jika COD: langsung masuk antrean admin dengan payment status pending
 * 3. Jika Midtrans: generate Snap token → popup pembayaran → callback update status
 */
class PaymentController extends Controller
{
    public function __construct()
    {
        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized = config('midtrans.is_sanitized');
        MidtransConfig::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Tampilkan halaman pemilihan metode pembayaran.
     */
    public function create(Order $order): View|RedirectResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->order_status !== Order::STATUS_PENDING) {
            return redirect()
                ->route('orders.show', $order)
                ->with('info', 'Pesanan ini sudah diproses.');
        }

        // Jika sudah ada payment:
        // - Midtrans pending → boleh akses (reuse snap token / retry)
        // - Midtrans failed → boleh akses (retry dengan token baru)
        // - Non-midtrans → redirect ke order detail
        if ($order->payment !== null) {
            $payment = $order->payment;
            $isMidtrans = $payment->payment_method === Payment::METHOD_MIDTRANS;
            $canRetry = in_array($payment->payment_status, [Payment::STATUS_PENDING, Payment::STATUS_FAILED]);

            if (! ($isMidtrans && $canRetry)) {
                return redirect()
                    ->route('orders.show', $order)
                    ->with('info', 'Pembayaran sudah tercatat untuk pesanan ini.');
            }
        }

        $order->load('items.product');

        return view('payments.create', [
            'order' => $order,
            'paymentMethods' => Payment::getMethodLabels(),
            'midtransClientKey' => config('midtrans.client_key'),
            'existingSnapToken' => $order->payment?->payment_status === Payment::STATUS_PENDING
                ? $order->payment->snap_token
                : null,
        ]);
    }

    /**
     * Proses dan simpan pembayaran.
     *
     * Untuk COD: simpan langsung.
     * Untuk Midtrans: buat payment record, generate Snap token, return JSON.
     */
    public function store(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->order_status !== Order::STATUS_PENDING) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Pesanan ini sudah diproses.'], 422);
            }

            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Pesanan ini sudah diproses.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:'.implode(',', Payment::getMethods())],
        ], [
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        // Midtrans flow: buat payment + generate Snap token
        if ($validated['payment_method'] === Payment::METHOD_MIDTRANS) {
            return $this->handleMidtransPayment($order);
        }

        // COD flow
        return $this->handleManualPayment($order, $validated);
    }

    /**
     * Handle Midtrans payment: buat record + generate Snap token.
     */
    private function handleMidtransPayment(Order $order): JsonResponse
    {
        try {
            $payment = DB::transaction(function () use ($order) {
                $lockedOrder = Order::lockForUpdate()->find($order->id);

                $existing = $lockedOrder->payment;

                // Jika ada payment non-midtrans, tolak
                if ($existing && $existing->payment_method !== Payment::METHOD_MIDTRANS) {
                    return null;
                }

                // Jika midtrans pending dengan snap_token valid, reuse
                if ($existing && $existing->payment_status === Payment::STATUS_PENDING && $existing->snap_token) {
                    return $existing;
                }

                // Jika midtrans failed, hapus dan buat ulang
                if ($existing && $existing->payment_status === Payment::STATUS_FAILED) {
                    $existing->delete();
                    $existing = null;
                }

                $lockedOrder->load('items.product', 'user');

                $orderId = 'GNG-'.$lockedOrder->id.'-'.time();

                $itemDetails = $lockedOrder->items->map(fn ($item) => [
                    'id' => (string) $item->product_id,
                    'price' => (int) round($item->price),
                    'quantity' => $item->quantity,
                    'name' => substr($item->product->name ?? 'Produk', 0, 50),
                ])->toArray();

                // gross_amount harus = sum(price * qty) dari item_details
                $grossAmount = collect($itemDetails)->sum(fn ($item) => $item['price'] * $item['quantity']);

                $params = [
                    'transaction_details' => [
                        'order_id' => $orderId,
                        'gross_amount' => $grossAmount,
                    ],
                    'item_details' => $itemDetails,
                    'customer_details' => [
                        'first_name' => $lockedOrder->user->name,
                        'email' => $lockedOrder->user->email,
                        'phone' => $lockedOrder->user->phone ?? '',
                    ],
                    'enabled_payments' => ['bca_va'],
                    'callbacks' => [
                        'finish' => route('orders.show', $lockedOrder->id),
                    ],
                ];

                $snapToken = Snap::getSnapToken($params);

                // Buat atau update payment record
                if ($existing) {
                    $existing->update([
                        'snap_token' => $snapToken,
                        'midtrans_transaction_id' => $orderId,
                    ]);

                    return $existing->fresh();
                }

                return Payment::create([
                    'order_id' => $lockedOrder->id,
                    'payment_method' => Payment::METHOD_MIDTRANS,
                    'payment_status' => Payment::STATUS_PENDING,
                    'payment_date' => now(),
                    'snap_token' => $snapToken,
                    'midtrans_transaction_id' => $orderId,
                ]);
            });

            if ($payment === null) {
                return response()->json(['error' => 'Pembayaran sudah tercatat dengan metode lain.'], 422);
            }

            return response()->json([
                'snap_token' => $payment->snap_token,
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Snap token error: '.$e->getMessage(), [
                'order_id' => $order->id,
            ]);

            return response()->json([
                'error' => 'Gagal membuat pembayaran. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Handle COD payment.
     */
    private function handleManualPayment(Order $order, array $validated): RedirectResponse
    {
        $message = DB::transaction(function () use ($order, $validated) {
            $lockedOrder = Order::lockForUpdate()->find($order->id);

            $existingPayment = $lockedOrder->payment;

            // Jika ada payment yang gagal (midtrans denied), hapus agar bisa ganti metode
            if ($existingPayment && $existingPayment->payment_status === Payment::STATUS_FAILED) {
                $existingPayment->delete();
            } elseif ($existingPayment) {
                return null;
            }

            Payment::create([
                'order_id' => $lockedOrder->id,
                'payment_method' => $validated['payment_method'],
                'payment_status' => Payment::STATUS_PENDING,
                'payment_date' => now(),
            ]);

            return 'Pesanan berhasil dibuat dengan metode COD. Siapkan uang tunai saat pesanan diantar.';
        });

        if ($message === null) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Pembayaran sudah tercatat.');
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', $message);
    }
}
