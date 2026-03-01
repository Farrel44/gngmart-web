@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8"
     x-data="cartPage()">

    {{-- Header --}}
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Keranjang Belanja</h1>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4">
            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
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

    @if($cart->items->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-20 bg-white rounded-2xl shadow-sm">
            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700">Keranjang Kosong</h3>
            <p class="text-sm text-gray-500 mt-1">Anda belum menambahkan produk ke keranjang.</p>
            <a href="{{ route('home') }}"
               class="mt-6 inline-block bg-red-600 text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-red-700 transition">
                Belanja Sekarang
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT: Cart Items --}}
            <div class="lg:col-span-2 space-y-4">
                @foreach($cart->items as $item)
                    @php
                        $imageUrl = $item->product->images->first()
                            ? asset($item->product->images->first()->image_url)
                            : asset('images/placeholder.png');
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm p-4 flex gap-4 items-start">
                        {{-- Thumbnail --}}
                        <a href="{{ route('products.show', $item->product) }}" class="flex-shrink-0">
                            <img src="{{ $imageUrl }}"
                                 alt="{{ $item->product->name }}"
                                 class="w-20 h-20 rounded-xl object-contain bg-gray-50">
                        </a>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('products.show', $item->product) }}"
                               class="text-sm font-semibold text-gray-900 hover:text-red-600 transition line-clamp-2">
                                {{ $item->product->name }}
                            </a>

                            {{-- Price --}}
                            <div class="mt-1">
                                @if($item->product->hasDiscount())
                                    <span class="text-xs text-gray-400 line-through">
                                        Rp {{ number_format($item->product->price, 0, ',', '.') }}
                                    </span>
                                    <span class="text-sm font-bold text-red-600 ml-1">
                                        Rp {{ number_format($item->product->getEffectivePrice(), 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-sm font-bold text-gray-900">
                                        Rp {{ number_format($item->product->price, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Quantity Controls + Delete --}}
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex items-center gap-2"
                                     x-data="{ qty: {{ $item->quantity }}, maxQty: {{ $item->product->stock }}, updating: false }">
                                    <button @click="if(qty > 1) { qty--; $dispatch('update-qty-{{ $item->id }}', { quantity: qty }) }"
                                            :disabled="qty <= 1 || updating"
                                            class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-10 text-center text-sm font-semibold" x-text="qty"></span>
                                    <button @click="if(qty < maxQty) { qty++; $dispatch('update-qty-{{ $item->id }}', { quantity: qty }) }"
                                            :disabled="qty >= maxQty || updating"
                                            class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>

                                    {{-- Hidden form for AJAX-less fallback: actual submit via JS --}}
                                    <form action="{{ route('cart.update', $item) }}" method="POST"
                                          id="update-form-{{ $item->id }}"
                                          @update-qty-{{ $item->id }}.window="updating = true; $el.querySelector('input[name=quantity]').value = $event.detail.quantity; $el.submit()">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $item->quantity }}">
                                    </form>
                                </div>

                                <div class="flex items-center gap-4">
                                    {{-- Subtotal --}}
                                    <span class="text-sm font-bold text-gray-900">
                                        Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                                    </span>

                                    {{-- Delete --}}
                                    <form action="{{ route('cart.destroy', $item) }}" method="POST"
                                          onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-500 transition" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Kosongkan Keranjang --}}
                <div class="flex justify-start">
                    <form action="{{ route('cart.clear') }}" method="POST"
                          onsubmit="return confirm('Kosongkan semua item di keranjang?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium transition">
                            Kosongkan Keranjang
                        </button>
                    </form>
                </div>
            </div>

            {{-- RIGHT: Ringkasan Pesanan --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Ringkasan Pesanan</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Total Item</span>
                            <span class="font-medium text-gray-900">{{ $cart->getTotalItems() }} barang</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Total Harga</span>
                            <span class="font-bold text-gray-900">Rp {{ number_format($cart->getTotalPrice(), 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <hr class="my-4 border-gray-100">

                    <div class="flex justify-between items-center mb-4">
                        <span class="text-base font-bold text-gray-900">Total</span>
                        <span class="text-lg font-bold text-red-600">
                            Rp {{ number_format($cart->getTotalPrice(), 0, ',', '.') }}
                        </span>
                    </div>

                    <a href="{{ route('checkout.index') }}"
                       class="block w-full text-center bg-red-600 text-white font-semibold py-3 rounded-xl hover:bg-red-700 transition">
                        Checkout
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function cartPage() {
        return {};
    }
</script>
@endsection
