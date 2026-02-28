{{--
    Search Results Page

    Menampilkan grid produk berdasarkan keyword pencarian.
    Menggunakan layout dan card design yang sama dengan category show page.
    Data: $products (eager-loaded with images, category), $query
--}}

@extends('layouts.app')

@section('content')

<div class="max-w-screen-xl mx-auto px-4 sm:px-6 pt-24 pb-12">

    {{-- Breadcrumb --}}
    <nav class="mb-6 text-sm text-gray-500 flex items-center gap-2">
        <a href="{{ route('home') }}" class="hover:text-red-600 transition">Beranda</a>
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-800 font-medium">Hasil Pencarian</span>
    </nav>

    {{-- Page header --}}
    <div class="mb-8 flex items-center gap-3">
        <svg class="w-7 h-7 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                Hasil pencarian untuk: "<span class="text-red-600">{{ $query }}</span>"
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Menampilkan {{ $products->count() }} produk
            </p>
        </div>
    </div>

    {{-- Product grid --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6">
        @forelse($products as $product)
            @php
                $imageUrl = $product->images->first()
                    ? asset($product->images->first()->image_url)
                    : asset('images/placeholder.png');

                $effectivePrice = $product->getEffectivePrice();
                $discountPct = $product->getDiscountPercentage();
                $hasDiscount = $discountPct > 0;
                $promo = $product->getBestActivePromotion();

                // Badge logic: promo aktif menggunakan label PROMO (orange),
                // selain itu gunakan label sesuai karakter kategori produk
                $badgeConfig = null;
                if ($promo) {
                    $badgeConfig = ['text' => 'PROMO', 'bg' => 'bg-orange-100', 'color' => 'text-orange-700'];
                } else {
                    $lowerCat = strtolower($product->category->name ?? '');
                    $badgeConfig = match(true) {
                        str_contains($lowerCat, 'buah') || str_contains($lowerCat, 'sayur'),
                        str_contains($lowerCat, 'daging') || str_contains($lowerCat, 'seafood')
                            => ['text' => 'SEGAR', 'bg' => 'bg-green-100', 'color' => 'text-green-700'],
                        str_contains($lowerCat, 'minuman')
                            => ['text' => 'MINUMAN', 'bg' => 'bg-teal-100', 'color' => 'text-teal-700'],
                        str_contains($lowerCat, 'roti') || str_contains($lowerCat, 'bakery')
                            => ['text' => 'BAKERY', 'bg' => 'bg-yellow-100', 'color' => 'text-yellow-700'],
                        str_contains($lowerCat, 'ringan') || str_contains($lowerCat, 'makanan')
                            => ['text' => 'SNACK', 'bg' => 'bg-amber-100', 'color' => 'text-amber-700'],
                        str_contains($lowerCat, 'dapur')
                            => ['text' => 'DAPUR', 'bg' => 'bg-blue-100', 'color' => 'text-blue-700'],
                        default
                            => ['text' => strtoupper($product->category->name ?? 'PRODUK'), 'bg' => 'bg-gray-100', 'color' => 'text-gray-700'],
                    };
                }
            @endphp

            <a href="{{ route('products.show', $product->slug) }}"
               class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-4 group flex flex-col h-full">

                {{-- Product image --}}
                <div class="relative mb-3">
                    <img src="{{ $imageUrl }}"
                         alt="{{ $product->name }}"
                         class="h-32 w-full object-contain rounded-xl bg-gray-50">

                    {{-- Discount badge --}}
                    @if($hasDiscount)
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                            -{{ $discountPct }}%
                        </span>
                    @endif
                </div>

                {{-- Category badge --}}
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full self-start mb-1 {{ $badgeConfig['bg'] }} {{ $badgeConfig['color'] }}">
                    {{ $badgeConfig['text'] }}
                </span>

                {{-- Product name --}}
                <h3 class="font-semibold text-gray-800 text-sm mt-1 line-clamp-2 group-hover:text-red-600 transition">
                    {{ $product->name }}
                </h3>

                {{-- Weight --}}
                @if($product->weight)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $product->weight }}</p>
                @endif

                {{-- Price --}}
                <div class="mt-auto pt-2">
                    @if($hasDiscount)
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

                    @if($promo)
                        <span class="text-[10px] bg-red-50 text-red-600 font-medium px-1.5 py-0.5 rounded mt-1 inline-block">
                            {{ $promo->name }}
                        </span>
                    @endif
                </div>

                {{-- Buy button --}}
                <button class="w-full bg-red-500 text-white py-2 rounded-xl text-sm font-semibold hover:bg-red-600 transition mt-3"
                        onclick="event.preventDefault();">
                    Beli
                </button>
            </a>
        @empty
            {{-- Empty state --}}
            <div class="col-span-full text-center py-16 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <p class="text-lg font-medium">Tidak ada produk untuk "{{ $query }}"</p>
                <a href="{{ route('home') }}" class="text-red-600 text-sm font-semibold hover:underline mt-2 inline-block">
                    Lihat Semua Produk
                </a>
            </div>
        @endforelse
    </div>

</div>

@endsection
