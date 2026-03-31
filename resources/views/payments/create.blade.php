@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- Header --}}
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Pembayaran</h1>

    {{-- Flash & Error Messages --}}
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
            <ul class="text-sm text-red-700 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Midtrans error message container --}}
    <div id="midtrans-error" class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 hidden">
        <p class="text-sm text-red-700 font-medium" id="midtrans-error-text"></p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- LEFT: Ringkasan Pesanan --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Ringkasan Pesanan
            </h2>

            <div class="text-sm text-gray-500 mb-4">
                <p class="font-semibold text-gray-900">Order #{{ $order->id }}</p>
                <p>{{ $order->order_date->format('d M Y, H:i') }}</p>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($order->items as $item)
                    <div class="flex justify-between py-3 text-sm">
                        <div>
                            <span class="text-gray-900">{{ $item->product->name ?? 'Produk Dihapus' }}</span>
                            <span class="text-gray-400 text-xs ml-1">(x{{ $item->quantity }})</span>
                        </div>
                        <span class="font-medium text-gray-900">
                            Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>

            <hr class="my-3 border-gray-100">

            <div class="flex justify-between text-base font-bold text-gray-900">
                <span>Total</span>
                <span class="text-red-600">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100">
                <h4 class="text-xs font-bold text-gray-500 uppercase mb-1">Alamat Pengiriman</h4>
                <p class="text-sm text-gray-700">{{ $order->address_shipment }}</p>
            </div>
        </div>

        {{-- RIGHT: Pilih Metode Pembayaran --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Pilih Metode Pembayaran
            </h2>

            <form method="POST" action="{{ route('payment.store', $order) }}" id="payment-form">
                @csrf

                {{-- Payment Method Options --}}
                <div class="space-y-3 mb-6">
                    @foreach($paymentMethods as $method => $label)
                        <label class="flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition
                                      {{ old('payment_method') === $method ? 'border-red-500 bg-red-50' : 'border-gray-200' }}">
                            <input type="radio"
                                   name="payment_method"
                                   value="{{ $method }}"
                                   class="h-4 w-4 text-red-600 focus:ring-red-500"
                                   {{ old('payment_method') === $method ? 'checked' : '' }}
                                   onchange="togglePaymentMethod(this.value)">
                            <div class="ml-3">
                                <span class="font-medium text-sm text-gray-900">{{ $label }}</span>
                                @if($method === 'cod')
                                    <p class="text-xs text-gray-500">Bayar tunai saat pesanan diantar</p>
                                @elseif($method === 'midtrans')
                                    <p class="text-xs text-gray-500">Bayar via Virtual Account BCA (otomatis terverifikasi)</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>

                {{-- COD Info --}}
                <div id="cod-info-section" class="hidden mb-6 bg-yellow-50 rounded-xl p-3">
                    <p class="text-sm text-yellow-800">
                        <strong>Catatan COD:</strong> Siapkan uang pas sebesar
                        <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                        saat pesanan diantar.
                    </p>
                </div>

                {{-- Midtrans Info --}}
                <div id="midtrans-info-section" class="hidden mb-6 bg-blue-50 rounded-xl p-3">
                    <p class="text-sm text-blue-800">
                        <strong>Transfer BCA Virtual Account:</strong> Anda akan diarahkan ke halaman pembayaran Midtrans.
                        Pembayaran akan otomatis terverifikasi setelah transfer berhasil.
                    </p>
                </div>

                {{-- Submit for non-midtrans --}}
                <button type="submit"
                        id="submit-btn"
                        disabled
                        class="w-full bg-red-600 text-white font-semibold py-3 rounded-xl hover:bg-red-700 transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Konfirmasi Pembayaran
                </button>

                {{-- Midtrans pay button (hidden by default) --}}
                <button type="button"
                        id="midtrans-pay-btn"
                        class="hidden w-full bg-red-600 text-white font-semibold py-3 rounded-xl hover:bg-red-700 transition"
                        onclick="payWithMidtrans()">
                    Bayar dengan BCA Virtual Account
                </button>
            </form>
        </div>
    </div>

    {{-- Back Link --}}
    <div class="mt-6">
        <a href="{{ route('orders.index') }}" class="inline-flex items-center text-sm text-red-600 hover:text-red-700 font-medium transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pesanan
        </a>
    </div>
</div>

{{-- Midtrans Snap.js (sandbox) --}}
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ $midtransClientKey }}"></script>

<script>
    const selectedMethod = { value: null };

    function togglePaymentMethod(method) {
        selectedMethod.value = method;

        const codSection = document.getElementById('cod-info-section');
        const midtransSection = document.getElementById('midtrans-info-section');
        const submitBtn = document.getElementById('submit-btn');
        const midtransBtn = document.getElementById('midtrans-pay-btn');

        // Reset all sections
        codSection.classList.add('hidden');
        midtransSection.classList.add('hidden');

        if (method === 'midtrans') {
            midtransSection.classList.remove('hidden');
            submitBtn.classList.add('hidden');
            midtransBtn.classList.remove('hidden');
        } else if (method === 'cod') {
            codSection.classList.remove('hidden');
            submitBtn.classList.remove('hidden');
            midtransBtn.classList.add('hidden');
            submitBtn.disabled = false;
        }
    }

    function payWithMidtrans() {
        const btn = document.getElementById('midtrans-pay-btn');
        const errorDiv = document.getElementById('midtrans-error');
        const errorText = document.getElementById('midtrans-error-text');

        btn.disabled = true;
        btn.textContent = 'Memproses...';
        errorDiv.classList.add('hidden');

        @if($existingSnapToken)
            // Sudah ada snap token, langsung buka popup
            openSnapPopup('{{ $existingSnapToken }}');
            btn.disabled = false;
            btn.textContent = 'Bayar dengan BCA Virtual Account';
            return;
        @endif

        // Request Snap token dari server
        fetch('{{ route("payment.store", $order) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                payment_method: 'midtrans',
            }),
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Bayar dengan BCA Virtual Account';

            if (data.error) {
                errorText.textContent = data.error;
                errorDiv.classList.remove('hidden');
                return;
            }

            if (data.snap_token) {
                openSnapPopup(data.snap_token);
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.textContent = 'Bayar dengan BCA Virtual Account';
            errorText.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
            errorDiv.classList.remove('hidden');
        });
    }

    function openSnapPopup(token) {
        window.snap.pay(token, {
            onSuccess: function(result) {
                window.location.href = '{{ route("orders.show", $order) }}';
            },
            onPending: function(result) {
                window.location.href = '{{ route("orders.show", $order) }}';
            },
            onError: function(result) {
                const errorDiv = document.getElementById('midtrans-error');
                const errorText = document.getElementById('midtrans-error-text');
                errorText.textContent = 'Pembayaran gagal. Silakan coba lagi.';
                errorDiv.classList.remove('hidden');
            },
            onClose: function() {
                // User menutup popup tanpa menyelesaikan pembayaran
            }
        });
    }

    // Re-apply state if old value exists (validation failure)
    document.addEventListener('DOMContentLoaded', function() {
        const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
        if (checkedRadio) {
            togglePaymentMethod(checkedRadio.value);
        }
    });
</script>
@endsection
