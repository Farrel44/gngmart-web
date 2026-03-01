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

            <form method="POST" action="{{ route('payment.store', $order) }}" enctype="multipart/form-data" id="payment-form">
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
                                   onchange="toggleProofUpload(this.value)">
                            <div class="ml-3">
                                <span class="font-medium text-sm text-gray-900">{{ $label }}</span>
                                @if($method === 'cod')
                                    <p class="text-xs text-gray-500">Bayar tunai saat pesanan diantar</p>
                                @elseif($method === 'transfer')
                                    <p class="text-xs text-gray-500">Transfer ke rekening toko, upload bukti bayar</p>
                                @elseif($method === 'ewallet')
                                    <p class="text-xs text-gray-500">OVO, GoPay, DANA, dll — upload screenshot</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>

                {{-- Upload Bukti Bayar (transfer/ewallet only) --}}
                <div id="proof-upload-section" class="hidden mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Unggah Bukti Pembayaran <span class="text-red-500">*</span>
                    </label>

                    <div class="mb-3 bg-red-50 rounded-xl p-3 text-sm">
                        <p class="font-medium text-red-800 mb-1">Informasi Transfer:</p>
                        <p class="text-red-700">Bank BCA: 1234567890</p>
                        <p class="text-red-700">a.n. GNG Mart</p>
                    </div>

                    <input type="file"
                           name="payment_proof"
                           id="payment_proof"
                           accept="image/jpeg,image/png"
                           class="block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-xl file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-red-50 file:text-red-700
                                  hover:file:bg-red-100">
                    <p class="mt-1 text-xs text-gray-400">Format: JPG, PNG. Maksimal 2MB.</p>
                </div>

                {{-- COD Info --}}
                <div id="cod-info-section" class="hidden mb-6 bg-yellow-50 rounded-xl p-3">
                    <p class="text-sm text-yellow-800">
                        <strong>Catatan COD:</strong> Siapkan uang pas sebesar
                        <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                        saat pesanan diantar.
                    </p>
                </div>

                <button type="submit"
                        id="submit-btn"
                        disabled
                        class="w-full bg-red-600 text-white font-semibold py-3 rounded-xl hover:bg-red-700 transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Konfirmasi Pembayaran
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

<script>
    function toggleProofUpload(method) {
        const proofSection = document.getElementById('proof-upload-section');
        const codSection = document.getElementById('cod-info-section');
        const proofInput = document.getElementById('payment_proof');
        const submitBtn = document.getElementById('submit-btn');

        if (method === 'cod') {
            proofSection.classList.add('hidden');
            codSection.classList.remove('hidden');
            proofInput.removeAttribute('required');
        } else {
            proofSection.classList.remove('hidden');
            codSection.classList.add('hidden');
            proofInput.setAttribute('required', 'required');
        }

        submitBtn.disabled = false;
    }

    // Re-apply state if old value exists (validation failure)
    document.addEventListener('DOMContentLoaded', function() {
        const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
        if (checkedRadio) {
            toggleProofUpload(checkedRadio.value);
        }
    });
</script>
@endsection
