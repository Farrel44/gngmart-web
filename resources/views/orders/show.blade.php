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
    $isMidtransPending = $order->payment?->payment_method === 'midtrans'
        && $order->payment?->payment_status === 'pending';
@endphp

<div class="bg-white min-h-screen"
     x-data="orderStatusChecker()"
     x-init="init()">
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
        <span x-text="orderStatusLabel"
              :class="orderStatusBadgeClass"
              class="inline-flex self-start px-3 py-1 text-xs font-medium rounded-full">
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

    {{-- Success banner (shown after payment confirmed via polling) --}}
    <div x-show="paymentJustConfirmed" x-cloak
         class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h4 class="text-sm font-semibold text-green-800">Pembayaran Berhasil!</h4>
                <p class="text-sm text-green-700 mt-0.5">Pembayaran telah dikonfirmasi. Pesanan Anda akan segera diproses.</p>
            </div>
        </div>
    </div>

    {{-- Action Banners —— controlled by Alpine to react to status changes --}}
    {{-- Banner: Belum bayar / payment gagal --}}
    <div x-show="orderStatus === 'pending' && (!paymentStatus || paymentStatus === 'failed')" x-cloak
         class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <template x-if="paymentStatus === 'failed'">
                    <div>
                        <h4 class="text-sm font-semibold text-yellow-800">Pembayaran Gagal</h4>
                        <p class="text-sm text-yellow-700 mt-0.5">Pembayaran sebelumnya gagal. Silakan coba lagi dengan metode yang sama atau berbeda.</p>
                    </div>
                </template>
                <template x-if="paymentStatus !== 'failed'">
                    <div>
                        <h4 class="text-sm font-semibold text-yellow-800">Menunggu Pembayaran</h4>
                        <p class="text-sm text-yellow-700 mt-0.5">Silakan selesaikan pembayaran untuk memproses pesanan Anda.</p>
                    </div>
                </template>
            </div>
            <a href="{{ route('payment.create', $order) }}"
               class="inline-flex items-center justify-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition flex-shrink-0">
                <span x-text="paymentStatus === 'failed' ? 'Coba Lagi' : 'Bayar Sekarang'"></span>
            </a>
        </div>
    </div>

    {{-- Banner: Midtrans pending --}}
    <div x-show="paymentStatus === 'pending' && paymentMethod === 'midtrans'" x-cloak
         class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h4 class="text-sm font-semibold text-blue-800">Menunggu Pembayaran BCA VA</h4>
                <p class="text-sm text-blue-700 mt-0.5">Selesaikan pembayaran melalui BCA Virtual Account. Status akan otomatis terupdate.</p>
                <p x-show="polling" class="text-xs text-blue-600 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Mengecek status pembayaran...
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                @if ($order->payment?->snap_token)
                    <button id="continue-pay-btn"
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition"
                            onclick="continueMidtransPayment()">
                        Lanjutkan Pembayaran
                    </button>
                @endif
                <button @click="manualCheck()"
                        :disabled="checking"
                        class="inline-flex items-center justify-center px-4 py-2.5 border border-blue-300 text-blue-700 text-sm font-medium rounded-xl hover:bg-blue-100 transition disabled:opacity-50">
                    <svg x-show="!checking" class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <svg x-show="checking" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Cek Status
                </button>
            </div>
        </div>
    </div>

    {{-- Banner: COD pending --}}
    <div x-show="paymentStatus === 'pending' && paymentMethod === 'cod'" x-cloak
         class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <h4 class="text-sm font-semibold text-blue-800">Menunggu Verifikasi</h4>
        <p class="text-sm text-blue-700 mt-0.5">Pembayaran Anda sedang dalam proses verifikasi oleh admin. Silakan cek secara berkala.</p>
    </div>

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
                                    <th class="pb-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wide">Jml</th>
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
                                <span x-text="orderStatusLabel"
                                      :class="orderStatusBadgeClass"
                                      class="px-3 py-1 text-xs font-medium rounded-full">
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
                                    <span x-text="paymentStatusLabel"
                                          :class="paymentStatusBadgeClass"
                                          class="px-3 py-1 text-xs font-medium rounded-full">
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
                        <div x-show="paymentStatus === 'pending' && paymentMethod === 'midtrans'" x-cloak>
                            @if ($order->payment->snap_token)
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <button class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 rounded-xl transition"
                                            onclick="continueMidtransPayment()">
                                        Lanjutkan Pembayaran
                                    </button>
                                </div>
                            @endif
                        </div>
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
            <div x-show="orderStatus === 'pending'" x-cloak>
                <form method="POST" action="{{ route('orders.cancel', $order) }}"
                      onsubmit="return confirm('Yakin ingin membatalkan pesanan ini? Tindakan ini tidak dapat dibatalkan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-red-100 text-red-600 hover:bg-red-200 text-sm font-semibold rounded-xl transition">
                        Batalkan Pesanan
                    </button>
                </form>
            </div>
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

{{-- Midtrans Snap.js --}}
@if ($order->payment?->payment_method === 'midtrans' && $order->payment?->snap_token)
    <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        function continueMidtransPayment() {
            window.snap.pay('{{ $order->payment->snap_token }}', {
                onSuccess: function(result) {
                    // Trigger immediate status check then reload
                    if (window._orderChecker) window._orderChecker.manualCheck();
                    setTimeout(() => window.location.reload(), 1500);
                },
                onPending: function(result) {
                    // Start polling — payment might settle shortly
                    if (window._orderChecker) window._orderChecker.startPolling();
                },
                onError: function(result) {
                    alert('Pembayaran gagal. Silakan coba lagi.');
                },
                onClose: function() {
                    // User closed popup — start polling in case payment was completed
                    if (window._orderChecker) window._orderChecker.startPolling();
                }
            });
        }
    </script>
@endif

{{-- Alpine.js Order Status Checker --}}
<script>
    function orderStatusChecker() {
        return {
            orderStatus: '{{ $order->order_status }}',
            paymentStatus: '{{ $order->payment?->payment_status ?? '' }}',
            paymentMethod: '{{ $order->payment?->payment_method ?? '' }}',
            polling: false,
            checking: false,
            pollInterval: null,
            pollCount: 0,
            maxPolls: 60,
            paymentJustConfirmed: false,

            // Status label maps
            orderStatusLabels: {
                pending: 'Menunggu Pembayaran',
                paid: 'Sudah Dibayar',
                processing: 'Diproses',
                shipped: 'Dikirim',
                completed: 'Selesai',
                cancelled: 'Dibatalkan',
            },
            orderStatusClasses: {
                pending: 'bg-yellow-100 text-yellow-700',
                paid: 'bg-blue-100 text-blue-700',
                processing: 'bg-purple-100 text-purple-700',
                shipped: 'bg-cyan-100 text-cyan-700',
                completed: 'bg-green-100 text-green-700',
                cancelled: 'bg-red-100 text-red-700',
            },
            paymentStatusLabels: {
                pending: 'Menunggu Verifikasi',
                success: 'Berhasil',
                failed: 'Gagal',
            },
            paymentStatusClasses: {
                pending: 'bg-yellow-100 text-yellow-700',
                success: 'bg-green-100 text-green-700',
                failed: 'bg-red-100 text-red-700',
            },

            get orderStatusLabel() {
                return this.orderStatusLabels[this.orderStatus] || this.orderStatus;
            },
            get orderStatusBadgeClass() {
                return this.orderStatusClasses[this.orderStatus] || 'bg-gray-100 text-gray-800';
            },
            get paymentStatusLabel() {
                return this.paymentStatusLabels[this.paymentStatus] || this.paymentStatus || '-';
            },
            get paymentStatusBadgeClass() {
                return this.paymentStatusClasses[this.paymentStatus] || 'bg-gray-100 text-gray-800';
            },

            init() {
                window._orderChecker = this;

                // Auto-start polling if Midtrans payment is pending
                if (this.paymentMethod === 'midtrans' && this.paymentStatus === 'pending') {
                    this.startPolling();
                }
            },

            startPolling() {
                if (this.polling) return;
                this.polling = true;
                this.pollCount = 0;

                this.pollInterval = setInterval(() => {
                    this.pollCount++;
                    if (this.pollCount > this.maxPolls) {
                        this.stopPolling();
                        return;
                    }
                    this.fetchStatus();
                }, 5000); // Check every 5 seconds
            },

            stopPolling() {
                this.polling = false;
                if (this.pollInterval) {
                    clearInterval(this.pollInterval);
                    this.pollInterval = null;
                }
            },

            async manualCheck() {
                this.checking = true;
                await this.fetchStatus();
                this.checking = false;
            },

            async fetchStatus() {
                try {
                    const response = await fetch('{{ route("orders.checkStatus", $order) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) return;

                    const data = await response.json();

                    const oldPaymentStatus = this.paymentStatus;

                    this.orderStatus = data.order_status;
                    this.paymentStatus = data.payment_status || '';

                    // Payment just changed to success
                    if (data.changed && data.payment_status === 'success') {
                        this.paymentJustConfirmed = true;
                        this.stopPolling();
                    }

                    // Stop polling if status is no longer pending
                    if (this.paymentStatus !== 'pending') {
                        this.stopPolling();
                    }

                } catch (e) {
                    // Silently fail, will retry on next poll
                }
            },

            destroy() {
                this.stopPolling();
            }
        };
    }
</script>

@endsection
