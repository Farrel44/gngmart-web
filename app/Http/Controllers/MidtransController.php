<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk menangani notification/callback dari Midtrans.
 *
 * Midtrans mengirim HTTP POST notification ke URL ini setiap kali
 * status transaksi berubah (settlement, pending, expire, cancel, deny).
 *
 * Route ini harus:
 * - Bisa diakses publik (tanpa auth)
 * - Dikecualikan dari CSRF verification
 */
class MidtransController extends Controller
{
    /**
     * Handle notification dari Midtrans.
     */
    public function notification(Request $request): Response
    {
        $serverKey = config('midtrans.server_key');

        $payload = $request->all();

        Log::info('Midtrans notification received', [
            'order_id' => $payload['order_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
        ]);

        // Verifikasi signature key
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::warning('Midtrans notification: invalid signature', [
                'order_id' => $orderId,
            ]);

            return response('Invalid signature', 403);
        }

        // Cari payment berdasarkan midtrans_transaction_id
        $payment = Payment::where('midtrans_transaction_id', $orderId)->first();

        if (! $payment) {
            Log::warning('Midtrans notification: payment not found', [
                'order_id' => $orderId,
            ]);

            return response('Payment not found', 404);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        DB::transaction(function () use ($payment, $transactionStatus, $fraudStatus, $orderId) {
            $payment = Payment::lockForUpdate()->find($payment->id);
            $order = Order::lockForUpdate()->find($payment->order_id);

            // Jangan proses ulang jika payment sudah final
            if (in_array($payment->payment_status, [Payment::STATUS_SUCCESS, Payment::STATUS_FAILED])) {
                Log::info('Midtrans notification: payment already final', [
                    'order_id' => $orderId,
                    'status' => $payment->payment_status,
                ]);

                return;
            }

            match ($transactionStatus) {
                'capture' => $this->handleCapture($payment, $order, $fraudStatus),
                'settlement' => $this->handleSettlement($payment, $order),
                'pending' => $this->handlePending($payment),
                'deny', 'cancel', 'expire' => $this->handleFailure($payment, $order, $transactionStatus),
                default => Log::info('Midtrans notification: unhandled status', [
                    'order_id' => $orderId,
                    'status' => $transactionStatus,
                ]),
            };
        });

        return response('OK', 200);
    }

    private function handleCapture(Payment $payment, Order $order, string $fraudStatus): void
    {
        if ($fraudStatus === 'accept') {
            $payment->update(['payment_status' => Payment::STATUS_SUCCESS]);
            $order->transitionTo(Order::STATUS_PAID);
        } else {
            $payment->update(['payment_status' => Payment::STATUS_FAILED]);
        }
    }

    private function handleSettlement(Payment $payment, Order $order): void
    {
        $payment->update([
            'payment_status' => Payment::STATUS_SUCCESS,
            'payment_date' => now(),
        ]);
        $order->transitionTo(Order::STATUS_PAID);
    }

    private function handlePending(Payment $payment): void
    {
        // Tetap pending, tidak perlu update
        Log::info('Midtrans payment still pending', [
            'payment_id' => $payment->id,
        ]);
    }

    private function handleFailure(Payment $payment, Order $order, string $status): void
    {
        Log::info('Midtrans payment failed/cancelled/expired', [
            'payment_id' => $payment->id,
            'status' => $status,
        ]);

        $payment->update(['payment_status' => Payment::STATUS_FAILED]);

        // Expire dan cancel → batalkan order dan kembalikan stok
        // Deny → biarkan order pending agar user bisa retry dengan metode lain
        if (in_array($status, ['expire', 'cancel']) && $order->canBeCancelled()) {
            $order->transitionTo(Order::STATUS_CANCELLED);

            foreach ($order->load('items.product')->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
        }
    }
}
