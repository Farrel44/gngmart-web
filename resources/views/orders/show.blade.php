@extends('layouts.app')

@section('content')

@php
    $statusColors = [
        'pending' => 'bg-yellow-100 text-yellow-700',
        'paid' => 'bg-blue-100 text-blue-700',
        'processing' => 'bg-purple-100 text-purple-700',
        'shipped' => 'bg-cyan-100 text-cyan-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];
    $paymentColors = [
        'pending' => 'bg-yellow-100 text-yellow-700',
        'success' => 'bg-green-100 text-green-700',
        'failed' => 'bg-red-100 text-red-700',
    ];
@endphp

<div class="bg-white min-h-screen">
<div class="max-w-screen-xl mx-auto px-6 pt-24 pb-12">

    {{-- Breadcrumb --}}
    <nav class="mb-6 bg-white border border-gray-200 rounded-xl px-5 py-3 text-sm text-gray-500 flex items-center gap-2 flex-wrap shadow-sm">
        <a href="{{ route('home') }}" class="hover:text-red-600 transition font-medium">Beranda</a>
        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
        <a href="{{ route('profile.show', ['tab' => 'transactions']) }}" class="hover:text-red-600 transition font-medium">Pesanan Saya</a>
        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
        <span class="text-gray-800 font-semibold">Pesanan #{{ $order->id }}</span>
    </nav>

    {{-- Page Title + Status --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Detail Pesanan #{{ $order->id }}</h1>
        <span class="inline-flex self-start px-3 py-1 text-xs font-medium rounded-full {{ $statusColors[$order->order_status] ?? 'bg-gray-100 text-gray-800' }}">
            {{ $order->getStatusLabel() }}
        </span>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4">
            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
        </div>
    @endif
    @if (session('info'))
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p class="text-sm text-blue-700 font-medium">{{ session('info') }}</p>
        </div>
    @endif

    {{-- Action Banners --}}
    @if ($order->order_status === 'pending' && (!$order->payment || $order->payment->payment_status === 'failed'))
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    @if ($order->payment?->payment_status === 'failed')
                        <h4 class="text-sm font-semibold text-yellow-800">Pembayaran Gagal</h4>
                        <p class="text-sm text-yellow-700 mt-0.5">Pembayaran sebelumnya gagal. Silakan coba lagi dengan metode yang sama atau berbeda.</p>
                    @else
                        <h4 class="text-sm font-semibold text-yellow-800">Menunggu Pembayaran</h4>
                        <p class="text-sm text-yellow-700 mt-0.5">Silakan selesaikan pembayaran untuk memproses pesanan Anda.</p>
                    @endif
                </div>
                <a href="{{ route('payment.create', $order) }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition flex-shrink-0">
                    {{ $order->payment?->payment_status === 'failed' ? 'Coba Lagi' : 'Bayar Sekarang' }}
                </a>
            </div>
        </div>
    @endif

    @if ($order->payment && $order->payment->payment_status === 'pending')
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            @if ($order->payment->payment_method === 'midtrans')
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-semibold text-blue-800">Menunggu Pembayaran BCA VA</h4>
                        <p class="text-sm text-blue-700 mt-0.5">Selesaikan pembayaran melalui BCA Virtual Account. Status akan otomatis terupdate.</p>
                    </div>
                    @if ($order->payment->snap_token)
                        <button id="continue-pay-btn"
                                class="inline-flex items-center justify-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition flex-shrink-0"
                                onclick="continueMidtransPayment()">
                            Lanjutkan Pembayaran
                        </button>
                    @endif
                </div>
            @else
                <h4 class="text-sm font-semibold text-blue-800">Menunggu Verifikasi</h4>
                <p class="text-sm text-blue-700 mt-0.5">Pembayaran Anda sedang dalam proses verifikasi oleh admin. Silakan cek secara berkala.</p>
            @endif
        </div>
    @endif

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column: Items + Address --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Order Items --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Pesanan</h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="pb-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Produk</th>
                                    <th class="pb-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wide">Harga</th>
                                    <th class="pb-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wide">Qty</th>
                                    <th class="pb-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wide">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($order->items as $item)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-4 pr-4">
                                            @if ($item->product)
                                                <a href="{{ route('products.show', $item->product->slug) }}"
                                                   class="text-sm font-medium text-gray-900 hover:text-red-600 transition">
                                                    {{ $item->product->name }}
                                                </a>
                                            @else
                                                <span class="text-sm text-gray-400 italic">Produk tidak tersedia</span>
                                            @endif
                                        </td>
                                        <td class="py-4 text-right text-sm text-gray-600">
                                            Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </td>
                                        <td class="py-4 text-center text-sm text-gray-600">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="py-4 text-right text-sm font-medium text-gray-800">
                                            Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Total --}}
                    <div class="mt-4 pt-4 border-t-2 border-gray-200 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-900">Total</span>
                        <span class="text-xl font-bold text-red-600">
                            Rp {{ number_format($order->total_price, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Shipping Address --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Alamat Pengiriman</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $order->address_shipment }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Order Info + Payment Info --}}
        <div class="space-y-6">

            {{-- Order Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Info Pesanan</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-500">No. Pesanan</dt>
                            <dd class="text-sm font-medium text-gray-800">#{{ $order->id }}</dd>
                        </div>
                        <div class="border-t border-gray-100"></div>
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-500">Tanggal</dt>
                            <dd class="text-sm font-medium text-gray-800">{{ $order->order_date->format('d M Y, H:i') }}</dd>
                        </div>
                        <div class="border-t border-gray-100"></div>
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd>
                                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusColors[$order->order_status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $order->getStatusLabel() }}
                                </span>
                            </dd>
                        </div>
                        <div class="border-t border-gray-100"></div>
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-500">Total</dt>
                            <dd class="text-sm font-bold text-red-600">Rp {{ number_format($order->total_price, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Payment Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Pembayaran</h3>
                </div>
                <div class="p-6">
                    @if ($order->payment)
                        <dl class="space-y-4">
                            <div class="flex justify-between items-center">
                                <dt class="text-sm text-gray-500">Metode</dt>
                                <dd class="text-sm font-medium text-gray-800">{{ $order->payment->getMethodLabel() }}</dd>
                            </div>
                            <div class="border-t border-gray-100"></div>
                            <div class="flex justify-between items-center">
                                <dt class="text-sm text-gray-500">Status</dt>
                                <dd>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full {{ $paymentColors[$order->payment->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $order->payment->getStatusLabel() }}
                                    </span>
                                </dd>
                            </div>
                            @if ($order->payment->payment_date)
                                <div class="border-t border-gray-100"></div>
                                <div class="flex justify-between items-center">
                                    <dt class="text-sm text-gray-500">Tanggal Bayar</dt>
                                    <dd class="text-sm font-medium text-gray-800">{{ $order->payment->payment_date->format('d M Y, H:i') }}</dd>
                                </div>
                            @endif
                            @if ($order->payment->midtrans_transaction_id)
                                <div class="border-t border-gray-100"></div>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm text-gray-500">ID Transaksi</dt>
                                    <dd class="text-xs font-mono text-gray-600 text-right break-all ml-4">{{ $order->payment->midtrans_transaction_id }}</dd>
                                </div>
                            @endif
                        </dl>

                        {{-- Payment Proof --}}
                        @if ($order->payment->payment_proof)
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-sm text-gray-500 mb-2">Bukti Pembayaran:</p>
                                <img src="{{ asset('storage/' . $order->payment->payment_proof) }}"
                                     alt="Bukti Pembayaran"
                                     class="w-full rounded-xl border border-gray-200">
                            </div>
                        @endif

                        {{-- Midtrans Continue Button (inside payment card) --}}
                        @if ($order->payment->payment_method === 'midtrans' && $order->payment->payment_status === 'pending' && $order->payment->snap_token)
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <button id="continue-pay-btn-sidebar"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 rounded-xl transition"
                                        onclick="continueMidtransPayment()">
                                    Lanjutkan Pembayaran
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <svg class="mx-auto h-10 w-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <p class="text-sm text-gray-500">Belum ada pembayaran</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Cancel Button --}}
            @if ($order->canBeCancelled())
                <form method="POST" action="{{ route('orders.cancel', $order) }}"
                      onsubmit="return confirm('Yakin ingin membatalkan pesanan ini? Tindakan ini tidak dapat dibatalkan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-red-100 text-red-600 hover:bg-red-200 text-sm font-semibold rounded-xl transition">
                        Batalkan Pesanan
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Back Link --}}
    <div class="mt-8 text-center">
        <a href="{{ route('profile.show', ['tab' => 'transactions']) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-red-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Riwayat Pesanan
        </a>
    </div>

</div>
</div>

@if ($order->payment?->payment_method === 'midtrans' && $order->payment?->snap_token && $order->payment?->payment_status === 'pending')
    <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        function continueMidtransPayment() {
            window.snap.pay('{{ $order->payment->snap_token }}', {
                onSuccess: function(result) {
                    window.location.reload();
                },
                onPending: function(result) {
                    window.location.reload();
                },
                onError: function(result) {
                    alert('Pembayaran gagal. Silakan coba lagi.');
                },
                onClose: function() {}
            });
        }
    </script>
@endif

@endsection
