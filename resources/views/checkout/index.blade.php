@extends('layouts.app')

@section('title', 'Checkout - Ringkasan Pesanan')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Ringkasan Pesanan</h1>

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

    <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
        @csrf

        {{-- Hidden inputs: kirim ID item yang dipilih ke store() --}}
        @foreach($items as $item)
            <input type="hidden" name="selected_items[]" value="{{ $item->id }}">
        @endforeach

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT: Detail Penerima + Pengiriman + Item List --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Detail Penerima --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Detail Penerima
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Nama</span>
                            <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">No. Telepon</span>
                            <p class="font-semibold text-gray-900">{{ $user->phone ?? '-' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-gray-500">Email</span>
                            <p class="font-semibold text-gray-900">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>

                {{-- Alamat Pengiriman --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Alamat Pengiriman
                    </h2>

                    <textarea name="address_shipment"
                              rows="3"
                              required
                              minlength="10"
                              maxlength="500"
                              placeholder="Masukkan alamat lengkap (jalan, RT/RW, kelurahan, kecamatan, kota, kode pos)..."
                              class="w-full rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm resize-none"
                    >{{ old('address_shipment', $user->address) }}</textarea>

                    @error('address_shipment')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Alamat otomatis terisi dari profil. Ubah jika perlu.</p>
                </div>

                {{-- Daftar Item --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Daftar Produk ({{ $items->sum('quantity') }} item)
                    </h2>

                    <div class="divide-y divide-gray-100">
                        @foreach($items as $item)
                            @php
                                $imageUrl = $item->product->images->first()
                                    ? asset('storage/' . $item->product->images->first()->image_url)
                                    : asset('images/placeholder.png');
                            @endphp
                            <div class="flex gap-4 py-4 first:pt-0 last:pb-0">
                                <div class="w-16 h-16 rounded-xl overflow-hidden relative bg-gray-100 flex-shrink-0">
                                    <div class="absolute inset-0 skeleton-shimmer"></div>
                                    <img src="{{ $imageUrl }}"
                                         alt="{{ $item->product->name }}"
                                         class="w-full h-full object-contain relative z-10 transition-opacity duration-300"
                                         style="opacity:0"
                                         loading="lazy"
                                         onload="this.style.opacity='1';this.previousElementSibling.remove()">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 line-clamp-2">
                                        {{ $item->product->name }}
                                    </p>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs text-gray-500">{{ $item->quantity }}x
                                            @if($item->product->hasDiscount())
                                                <span class="line-through text-gray-400 ml-1">Rp {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                                <span class="text-red-600 font-medium ml-1">Rp {{ number_format($item->product->getEffectivePrice(), 0, ',', '.') }}</span>
                                            @else
                                                Rp {{ number_format($item->product->price, 0, ',', '.') }}
                                            @endif
                                        </span>
                                        <span class="text-sm font-bold text-gray-900">
                                            Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Back link --}}
                <a href="{{ route('cart.index') }}" class="inline-flex items-center text-sm text-red-600 hover:text-red-700 font-medium transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali ke Keranjang
                </a>
            </div>

            {{-- RIGHT: Ringkasan + Submit --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Ringkasan Pesanan</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal ({{ $items->sum('quantity') }} item)</span>
                            <span class="font-medium text-gray-900">
                                Rp {{ number_format($items->sum(fn($i) => $i->getSubtotal()), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Pengiriman</span>
                            <span class="font-medium text-green-600">Gratis</span>
                        </div>
                    </div>

                    <hr class="my-4 border-gray-100">

                    <div class="flex justify-between items-center mb-6">
                        <span class="text-base font-bold text-gray-900">Total</span>
                        <span class="text-lg font-bold text-red-600">
                            Rp {{ number_format($items->sum(fn($i) => $i->getSubtotal()), 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Info --}}
                    <div class="mb-4 bg-red-50 rounded-xl p-3">
                        <p class="text-xs text-red-700">
                            <strong>Info:</strong> Setelah menekan tombol di bawah, Anda akan diarahkan ke halaman pembayaran.
                        </p>
                    </div>

                    <button type="submit" form="checkout-form"
                            class="w-full bg-red-600 text-white font-semibold py-3 rounded-xl hover:bg-red-700 transition">
                        Lanjut ke Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
