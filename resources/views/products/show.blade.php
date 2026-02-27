@extends('layouts.app')

@section('content')

<div class="max-w-screen-xl mx-auto px-6 py-8">
    {{-- Breadcrumb Navigation --}}
    <div class="mb-6 text-sm text-gray-600 flex items-center gap-2">
        <a href="{{ route('home') }}" class="hover:text-red-600 transition">Beranda</a>
        <span class="text-gray-400">●</span>
        <a href="{{ route('products.index') }}" class="hover:text-red-600 transition">{{ $product->category->name ?? 'Produk' }}</a>
        <span class="text-gray-400">●</span>
        <span class="text-gray-800 font-medium">{{ $product->name }}</span>
    </div>

    {{-- Product Detail Section --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        {{-- Image Gallery (Left: 2 cols) --}}
        <div class="md:col-span-2">
            {{-- Main Image --}}
            <div class="bg-gray-200 rounded-2xl overflow-hidden mb-4 h-80 flex items-center justify-center" id="mainImageContainer">
                @php
                    $imageUrl = $product->images->first() 
                        ? asset($product->images->first()->image_url) 
                        : asset('images/placeholder.png');
                @endphp
                <img src="{{ $imageUrl }}" 
                     alt="{{ $product->name }}"
                     class="w-full h-full object-contain"
                     id="mainImage">
            </div>

            {{-- Thumbnail Gallery --}}
            @if($product->images->count() > 1)
                <div class="flex gap-3">
                    @foreach($product->images as $index => $image)
                        <button class="thumbnail-btn border-2 rounded-lg overflow-hidden w-16 h-16 {{ $index === 0 ? 'border-red-600' : 'border-gray-300' }} hover:border-red-600 transition"
                                onclick="changeImage('{{ asset($image->image_url) }}', this)">
                            <img src="{{ asset($image->image_url) }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Product Info (Right: 1 col) --}}
        <div>
            {{-- Product Name --}}
            <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>

            {{-- Description Section --}}
            <div class="mb-6">
                <h3 class="font-semibold text-gray-800 mb-2">Deskripsi</h3>
                @if($product->description)
                    <p class="text-sm text-gray-600 mb-2">{{ Str::limit($product->description, 150) }}</p>
                @endif
                <a href="#" class="text-blue-600 text-sm font-medium hover:underline">Lihat Selengkapnya</a>
            </div>

            {{-- Quantity Selector --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-800 mb-2">Jumlah Pembelian</label>
                <div class="flex items-center gap-3 bg-gray-100 rounded-lg p-2 w-fit">
                    <button onclick="decreaseQty()" class="w-6 h-6 flex items-center justify-center hover:bg-gray-300 rounded transition text-gray-700">−</button>
                    <input type="number" id="quantity" value="1" min="1" class="w-12 text-center bg-transparent border-none focus:outline-none font-semibold" readonly>
                    <button onclick="increaseQty()" class="w-6 h-6 flex items-center justify-center hover:bg-gray-300 rounded transition text-gray-700">+</button>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="space-y-3">
                <button class="w-full bg-red-600 text-white font-semibold py-3 rounded-lg hover:bg-red-700 transition">
                    Beli Sekarang
                </button>
                <button class="w-full border-2 border-red-600 text-red-600 font-semibold py-3 rounded-lg hover:bg-red-50 transition">
                    Masukkan Keranjang
                </button>
            </div>

            {{-- Shipping Info --}}
            <div class="mt-8 space-y-3 text-sm">
                <div class="flex items-center gap-3">
                    <span class="text-xl">🚚</span>
                    <div>
                        <p class="text-gray-500">Ongkos Kirim</p>
                        <p class="text-gray-800">Dikirim oleh Kurir Toko</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xl">💜</span>
                    <div>
                        <p class="text-gray-500">Biaya Pengiriman</p>
                        <p class="text-gray-800 font-semibold">Gratis</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function changeImage(imageSrc, button) {
        document.getElementById('mainImage').src = imageSrc;
        document.querySelectorAll('.thumbnail-btn').forEach(btn => {
            btn.classList.remove('border-red-600');
            btn.classList.add('border-gray-300');
        });
        button.classList.remove('border-gray-300');
        button.classList.add('border-red-600');
    }

    function increaseQty() {
        const qtyInput = document.getElementById('quantity');
        qtyInput.value = parseInt(qtyInput.value) + 1;
    }

    function decreaseQty() {
        const qtyInput = document.getElementById('quantity');
        if (parseInt(qtyInput.value) > 1) {
            qtyInput.value = parseInt(qtyInput.value) - 1;
        }
    }
</script>


@endsection
