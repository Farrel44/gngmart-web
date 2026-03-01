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

    @php
        // SVG path data per category keyword (Heroicons outline, 24x24 viewBox)
        // Memungkinkan icon yang sesuai untuk setiap kategori tanpa perlu kolom DB tambahan
        $catIconPaths = [
            'buah'    => 'M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z',
            'daging'  => 'M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z',
            'minuman' => 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5m4.75-11.396c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3m-5.55-12.196c.251.023.501.05.75.082M5 14.5l-1.202 1.202c-1.232 1.232-.65 3.318 1.067 3.611A48.309 48.309 0 0012 21c2.773 0 5.491-.235 8.135-.687 1.718-.293 2.3-2.379 1.067-3.61L19.8 15.3',
            'ringan'  => 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z',
            'roti'    => 'M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.379a48.474 48.474 0 00-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 013 20.496v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12',
            'dapur'   => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        ];
        $defaultIconPath = 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z';
    @endphp

    <div class="flex gap-4 overflow-x-auto pb-4 mb-12">
        @foreach($categories as $cat)
            @php
                // Match category name to icon keyword
                $lowerName = strtolower($cat->name);
                $iconPath = $defaultIconPath;
                foreach ($catIconPaths as $keyword => $path) {
                    if (str_contains($lowerName, $keyword)) {
                        $iconPath = $path;
                        break;
                    }
                }
            @endphp

            <a href="{{ route('categories.show', $cat) }}"
               class="flex items-center gap-3 bg-rose-50 hover:bg-rose-100 transition px-5 py-3 rounded-2xl min-w-max shadow-sm">
                <div class="w-8 h-8 flex items-center justify-center bg-red-100 rounded-full flex-shrink-0">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">
                    {{ $cat->name }}
                </span>
            </a>
        @endforeach
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
    <a href="{{ route('products.show', $product->slug) }}" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-4 group flex flex-col h-full">

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

        <span class="w-full bg-red-600 text-white py-2 rounded-full text-sm hover:bg-red-700 transition mt-auto block text-center">
            Beli
        </span>

    </a>
    @empty
        <div class="col-span-full text-center py-12 text-gray-500">
            <p class="text-lg">Belum ada produk tersedia.</p>
        </div>
    @endempty

</div>

@if($promoSlide)
<div class="max-w-screen-xl mx-auto px-6 mt-16">
    <div class="relative bg-gradient-to-r from-green-300 via-green-400 to-green-500
                rounded-3xl p-12 flex flex-col md:flex-row items-center overflow-hidden">

        {{-- Text --}}
        <div class="flex-1 pr-8 z-10">
            @if($promoSlide->subtitle)
                <p class="text-sm font-semibold text-green-800 uppercase mb-2">
                    {{ $promoSlide->subtitle }}
                </p>
            @endif

            @if($promoSlide->title)
                <h2 class="text-3xl font-bold text-gray-800 mb-4">
                    {{ $promoSlide->title }}
                </h2>
            @endif

            @if($promoSlide->description)
                <p class="text-gray-700 mb-6">
                    {{ $promoSlide->description }}
                </p>
            @endif

            @if($promoSlide->button_text && $promoSlide->button_url)
                <a href="{{ $promoSlide->button_url }}"
                   class="inline-block bg-red-600 text-white px-6 py-3 rounded-full
                          hover:bg-red-700 transition">
                    {{ $promoSlide->button_text }}
                </a>
            @endif
        </div>

        {{-- Image --}}
        @if($promoSlide->image_path)
            <img src="{{ asset('storage/' . $promoSlide->image_path) }}"
                 alt="{{ $promoSlide->title ?? 'Promo' }}"
                 class="absolute right-0 top-0 h-full w-auto max-w-md
                        object-cover rounded-2xl pointer-events-none">
        @endif

    </div>
</div>
@endif


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

