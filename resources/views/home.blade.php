@extends('layouts.app')

@section('content')

<div class="max-w-screen-xl mx-auto px-2">

    {{-- Carousel Banner: dinamis dari database, dikelola admin via Filament --}}
    @if($slides->count() > 0)
    <div class="relative rounded-3xl mb-12 shadow-lg overflow-hidden mt-20" id="carousel"
         x-data="carousel({{ $slides->count() }})" x-init="startAutoPlay()">

        {{-- Slide container --}}
        <div class="carousel-container relative h-72 md:h-80">
            @foreach($slides as $index => $slide)
                <div class="carousel-slide absolute inset-0 rounded-3xl overflow-hidden flex items-center transition-opacity duration-500"
                     :class="activeSlide === {{ $index }} ? 'opacity-100 z-10' : 'opacity-0 z-0'">

                    {{-- Background image --}}
                    <img src="{{ asset('storage/' . $slide->image_path) }}"
                         alt="{{ $slide->title ?? 'Promo banner' }}"
                         class="absolute inset-0 w-full h-full object-cover">

                    {{-- Overlay gradient untuk keterbacaan teks --}}
                    <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent"></div>

                    {{-- Konten teks --}}
                    <div class="relative z-10 px-10 md:px-16 max-w-xl">
                        @if($slide->subtitle)
                            <p class="text-sm font-semibold text-red-300 uppercase tracking-wide mb-2">
                                {{ $slide->subtitle }}
                            </p>
                        @endif

                        @if($slide->title)
                            <h2 class="text-2xl md:text-4xl font-bold text-white mb-3 leading-tight">
                                {{ $slide->title }}
                            </h2>
                        @endif

                        @if($slide->description)
                            <p class="text-white/80 text-sm md:text-base mb-6">
                                {{ $slide->description }}
                            </p>
                        @endif

                        @if($slide->button_text && $slide->button_url)
                            <a href="{{ $slide->button_url }}"
                               class="inline-block bg-red-600 text-white font-semibold px-6 py-3 rounded-full hover:bg-red-700 transition">
                                {{ $slide->button_text }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Dots Indicator --}}
        @if($slides->count() > 1)
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2 z-20">
                @foreach($slides as $index => $slide)
                    <button @click="goTo({{ $index }})"
                            class="w-3 h-3 rounded-full transition-all cursor-pointer"
                            :class="activeSlide === {{ $index }} ? 'bg-red-600 w-6' : 'bg-white/60 hover:bg-white/80'">
                    </button>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    <h2 class="text-2xl font-bold text-gray-800 mb-6">
    Kategori
</h2>

<div class="flex gap-4 overflow-x-auto pb-4 mb-12">

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            🍔
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Makanan & Minuman
        </span>
    </div>

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            🏠
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Kebutuhan Rumah Tangga
        </span>
    </div>

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            👜
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Aksesoris
        </span>
    </div>

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            📚
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Alat Tulis
        </span>
    </div>

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            💊
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Kesehatan
        </span>
    </div>

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            💄
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Kecantikan
        </span>
    </div>

</div>


    <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">
        Rekomendasi Mingguan
    </h2>
    <a href="#" class="text-blue-600 text-sm font-semibold hover:underline">
        Lihat Semua
    </a>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-20">

    @forelse($featuredProducts as $product)
    <a href="{{ route('products.show', $product->slug) }}" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-4 block group">

        @php
            $imageUrl = $product->images->first()
                ? asset($product->images->first()->image_url)
                : asset('images/placeholder.png');

            $effectivePrice = $product->getEffectivePrice();
            $discountPct = $product->getDiscountPercentage();
            $hasAnyDiscount = $discountPct > 0;
            $promo = $product->getBestActivePromotion();
        @endphp

        <div class="relative">
            <img src="{{ $imageUrl }}"
                 alt="{{ $product->name }}"
                 class="h-32 w-full object-cover rounded-xl mb-4 bg-gray-200">

            {{-- Badge diskon jika ada promo aktif atau discount_price --}}
            @if($hasAnyDiscount)
                <span class="absolute top-2 left-2 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                    -{{ $discountPct }}%
                </span>
            @endif
        </div>

        {{-- Label kategori --}}
        @if($product->category)
            <span class="text-xs font-bold text-green-500">
                {{ strtoupper($product->category->name) }}
            </span>
        @endif

        <h3 class="font-semibold text-gray-800 text-sm mt-1 line-clamp-2 group-hover:text-red-600 transition">
            {{ $product->name }}
        </h3>

        @if($product->weight)
            <p class="text-xs text-gray-500 mb-2">{{ $product->weight }}</p>
        @endif

        {{-- Harga: tampilkan harga coret jika ada diskon --}}
        <div class="mb-3">
            @if($hasAnyDiscount)
                <p class="text-xs text-gray-400 line-through">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </p>
                <p class="text-red-600 font-bold">
                    Rp {{ number_format($effectivePrice, 0, ',', '.') }}
                </p>
            @else
                <p class="text-red-600 font-bold">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </p>
            @endif

            {{-- Tampilkan nama promo jika ada --}}
            @if($promo)
                <span class="text-[10px] bg-red-50 text-red-600 font-medium px-1.5 py-0.5 rounded mt-1 inline-block">
                    {{ $promo->name }}
                </span>
            @endif
        </div>

        <button class="w-full bg-red-600 text-white py-2 rounded-full text-sm hover:bg-red-700 transition" onclick="event.preventDefault();">
            Beli
        </button>

    </a>
    @empty
        <div class="col-span-full text-center py-12 text-gray-500">
            <p class="text-lg">Belum ada produk tersedia.</p>
        </div>
    @endempty

</div>

    <!-- Promo Section -->
    <div class="max-w-screen-xl mx-auto px-6 mt-16">
        <div class="relative bg-gradient-to-r from-green-300 via-green-400 to-green-500 rounded-3xl p-12 flex flex-col md:flex-row items-center">
            <div class="flex-1 pr-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Hemat 30% untuk Produk Organik!</h2>
                <p class="text-gray-600 mb-6">Nikmati kesegaran alami dengan harga spesial minggu ini</p>
                <a href="#" class="inline-block bg-red-600 text-white px-6 py-3 rounded-full hover:bg-red-700 transition">
                    Belanja Sekarang
                </a>
            </div>
            <!-- image overlaps outside green box -->
            <img src="{{ asset('images/sayur.png') }}" alt="Promo Organik" class="absolute right-0 top-0 h-full w-auto max-w-md rounded-2xl object-cover">
        </div>
    </div>

{{-- Footer disediakan oleh layouts/app.blade.php --}}

</div>

{{-- Alpine.js carousel component --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('carousel', (totalSlides) => ({
            activeSlide: 0,
            total: totalSlides,
            timer: null,

            startAutoPlay() {
                this.timer = setInterval(() => this.next(), 5000);
            },

            stopAutoPlay() {
                if (this.timer) clearInterval(this.timer);
            },

            resetAutoPlay() {
                this.stopAutoPlay();
                this.startAutoPlay();
            },

            next() {
                this.activeSlide = (this.activeSlide + 1) % this.total;
            },

            prev() {
                this.activeSlide = (this.activeSlide - 1 + this.total) % this.total;
                this.resetAutoPlay();
            },

            goTo(index) {
                this.activeSlide = index;
                this.resetAutoPlay();
            }
        }));
    });
</script>

@endsection

