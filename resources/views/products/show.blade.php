@extends('layouts.app')

@section('content')

@php
    $effectivePrice = $product->getEffectivePrice();
    $discountPct = $product->getDiscountPercentage();
    $hasAnyDiscount = $discountPct > 0;
    $promo = $product->getBestActivePromotion();
    $firstImage = $product->images->first();
    $imageUrls = $product->images->map(fn($img) => asset('storage/' . $img->image_url))->toArray();
    $fallbackImage = asset('images/logo.png');
@endphp

<div class="bg-white min-h-screen">
<div class="max-w-screen-xl mx-auto px-6 pt-24 pb-12"
     x-data="productDetail({
         images: {{ Js::from($imageUrls) }},
         fallback: '{{ $fallbackImage }}',
         stock: {{ $product->stock }},
         price: {{ $effectivePrice }}
     })">
    <nav class="mb-6 bg-white border border-gray-200 rounded-xl px-5 py-3 text-sm text-gray-500 flex items-center gap-2 flex-wrap shadow-sm">
        <a href="{{ route('home') }}" class="hover:text-red-600 transition font-medium">Beranda</a>

        {{-- Green dot separator --}}
        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>

        @if($product->category)
            <a href="{{ route('products.index', ['category' => $product->category->id]) }}"
               class="hover:text-red-600 transition font-medium">
                {{ $product->category->name }}
            </a>
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
        @endif

        <span class="text-gray-800 font-semibold truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4">
                {{-- Main product image with soft shadow --}}
                <div class="bg-white rounded-xl overflow-hidden aspect-square flex items-center justify-center relative">
                    <div class="absolute inset-0 skeleton-shimmer" x-ref="mainImgSkeleton"></div>
                    <img :src="currentImage"
                         alt="{{ $product->name }}"
                         class="w-full h-full object-contain p-2 relative z-10 transition-opacity duration-300"
                         :style="imgLoaded ? 'opacity:1' : 'opacity:0'"
                         @load="imgLoaded = true; $refs.mainImgSkeleton?.remove()"
                         x-effect="if(currentImage) imgLoaded = false">
                </div>

                {{-- Thumbnail strip --}}
                @if($product->images->count() > 1)
                    <div class="flex gap-3 mt-4 justify-center">
                        @foreach($product->images as $index => $image)
                            <button @click="selectImage({{ $index }})"
                                    class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-all relative"
                                    :class="activeIndex === {{ $index }}
                                        ? 'border-red-600 shadow-sm'
                                        : 'border-gray-200 hover:border-gray-400'">
                                <div class="absolute inset-0 skeleton-shimmer"></div>
                                <img src="{{ asset('storage/' . $image->image_url) }}"
                                     alt="{{ $product->name }} - {{ $index + 1 }}"
                                     class="w-full h-full object-cover relative z-10 transition-opacity duration-200"
                                     style="opacity:0"
                                     loading="lazy"
                                     onload="this.style.opacity='1';this.previousElementSibling.remove()">
                            </button>
                        @endforeach
                    </div>
                @endif
        </div>  

        {{-- ============================================== --}}
        {{-- CENTER: Product Info (~5 cols)                --}}
        {{-- ============================================== --}}
        <div class="lg:col-span-5 space-y-5">

            {{-- Product name (large bold) --}}
            <h1 class="text-2xl lg:text-[28px] font-bold text-gray-900 leading-tight">
                {{ $product->name }}
            </h1>

            {{-- Price section --}}
            <div class="space-y-1">
                @if($hasAnyDiscount)
                    <div class="flex items-center gap-3">
                        <span class="text-2xl lg:text-3xl font-bold text-red-600">
                            Rp {{ number_format($effectivePrice, 0, ',', '.') }}
                        </span>
                        <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full">
                            -{{ $discountPct }}%
                        </span>
                    </div>
                    <p class="text-sm text-gray-400 line-through">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </p>
                    @if($promo)
                        <span class="inline-block text-xs bg-red-50 text-red-600 font-semibold px-2.5 py-1 rounded-lg mt-1">
                            {{ $promo->name }}
                        </span>
                    @endif
                @else
                    <span class="text-2xl lg:text-3xl font-bold text-gray-900">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </span>
                @endif
            </div>

            {{-- Brand & Instant Shipping card --}}
            <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">GnG Mart Official</p>
                        <p class="text-xs text-gray-500">Brand Toko Resmi</p>
                    </div>
                </div>
                <div class="border-t border-gray-100 pt-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Pengiriman Instan</p>
                        <p class="text-xs text-gray-500">Sampai di hari yang sama</p>
                    </div>
                </div>
            </div>

            {{-- Weight info --}}
            @if($product->weight)
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l3 9a5.002 5.002 0 01-6.001 0M18 7l-3 9m-3-9l-6-2"/>
                    </svg>
                    <span>Berat: {{ $product->weight }}</span>
                </div>
            @endif

            {{-- Description section (bullet list style) --}}
            @if($product->description)
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Deskripsi Produk</h3>

                    {{-- Preview: first 200 chars --}}
                    <div x-show="!showFullDesc">
                        <div class="text-sm text-gray-600 leading-relaxed space-y-1.5">
                            @foreach(array_slice(array_filter(explode("\n", $product->description)), 0, 3) as $line)
                                <div class="flex items-start gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5 flex-shrink-0"></span>
                                    <span>{{ Str::limit(trim($line), 100) }}</span>
                                </div>
                            @endforeach
                        </div>
                        @if(strlen($product->description) > 200)
                            <button @click="showFullDesc = true"
                                    class="text-blue-600 text-sm font-medium hover:underline mt-3 inline-flex items-center gap-1">
                                Lihat Selengkapnya
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        @endif
                    </div>

                    {{-- Full description --}}
                    <div x-show="showFullDesc" x-cloak>
                        <div class="text-sm text-gray-600 leading-relaxed space-y-1.5">
                            @foreach(array_filter(explode("\n", $product->description)) as $line)
                                <div class="flex items-start gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5 flex-shrink-0"></span>
                                    <span>{{ trim($line) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <button @click="showFullDesc = false"
                                class="text-blue-600 text-sm font-medium hover:underline mt-3 inline-flex items-center gap-1">
                            Sembunyikan
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Category tag --}}
            @if($product->category)
                <div>
                    <a href="{{ route('products.index', ['category' => $product->category->id]) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-green-600 bg-green-50 border border-green-200 px-3 py-1.5 rounded-full uppercase tracking-wide hover:bg-green-100 transition">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                        {{ $product->category->name }}
                    </a>
                </div>
            @endif
        </div>

        <div class="lg:col-span-3">
            <div class="lg:sticky lg:top-24 space-y-4">

                {{-- Main purchase card --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-md space-y-4">

                    {{-- Unit price --}}
                    <div>
                        @if($hasAnyDiscount)
                            <div class="flex items-center gap-2">
                                <span class="text-xl font-bold text-red-600">
                                    Rp {{ number_format($effectivePrice, 0, ',', '.') }}
                                </span>
                                <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                    -{{ $discountPct }}%
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 line-through mt-0.5">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </p>
                        @else
                            <span class="text-xl font-bold text-gray-900">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    {{-- Stock indicator --}}
                    <div class="flex items-center gap-2">
                        @if($product->stock > 0)
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <span class="text-xs text-gray-600">
                                Stok: <strong class="text-gray-800">{{ $product->stock }}</strong>
                            </span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            <span class="text-xs text-red-600 font-medium">Stok habis</span>
                        @endif
                    </div>

                    @if($product->stock > 0)
                        {{-- Action buttons --}}
                        <div class="space-y-2.5 pt-2 border-t border-gray-100">
                            @auth
                                {{-- Beli Sekarang: POST ke cart lalu redirect checkout --}}
                                <form method="POST" action="{{ route('cart.store') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="redirect" value="checkout">
                                    <button type="submit"
                                            class="w-full bg-red-600 text-white font-semibold py-2.5 rounded-xl hover:bg-red-700 active:bg-red-800 transition text-sm shadow-sm">
                                        Beli Sekarang
                                    </button>
                                </form>

                                {{-- Masukkan Keranjang: AJAX POST, tampilkan popup --}}
                                <button type="button"
                                        @click="addToCart()"
                                        :disabled="addingToCart"
                                        class="w-full border-2 border-red-600 text-red-600 font-semibold py-2.5 rounded-xl hover:bg-red-50 active:bg-red-100 transition text-sm disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                    <template x-if="addingToCart">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </template>
                                    <span x-text="addingToCart ? 'Menambahkan...' : 'Masukkan Keranjang'"></span>
                                </button>
                            @else
                                {{-- Guest: redirect ke login karena fitur ini butuh autentikasi --}}
                                <a href="{{ route('login') }}"
                                   class="block w-full text-center bg-red-600 text-white font-semibold py-2.5 rounded-xl hover:bg-red-700 active:bg-red-800 transition text-sm shadow-sm">
                                    Beli Sekarang
                                </a>
                                <a href="{{ route('login') }}"
                                   class="block w-full text-center border-2 border-red-600 text-red-600 font-semibold py-2.5 rounded-xl hover:bg-red-50 active:bg-red-100 transition text-sm">
                                    Masukkan Keranjang
                                </a>
                            @endauth
                        </div>
                    @else
                        <button disabled
                                class="w-full bg-gray-300 text-gray-500 font-semibold py-2.5 rounded-xl cursor-not-allowed text-sm">
                            Stok Habis
                        </button>
                    @endif
                </div>

                {{-- Shipping info card --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm space-y-3">
                    <h4 class="text-xs font-semibold text-gray-800 uppercase tracking-wide">Pengiriman</h4>

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center flex-shrink-0 ">
                            @include('icons.truck')
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-800">Kurir Toko</p>
                            <p class="text-[11px] text-gray-500">Dikirim oleh GnG Mart</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center flex-shrink-0">
                            @include('icons.handmoney')
                        </div>
                        <div>
                            <p class="text-xs font-medium text-white-700">Gratis Ongkir</p>
                            <p class="text-[11px] text-gray-500">Semua area pengiriman</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Validation errors from cart actions --}}
    @if($errors->any())
        <div class="mt-6 bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-sm text-red-700 font-medium">
                {{ $errors->first() }}
            </p>
        </div>
    @endif

    {{-- Success message --}}
    @if(session('success'))
        <div class="mt-6 bg-green-50 border border-green-200 rounded-xl p-4">
            <p class="text-sm text-green-700 font-medium">
                {{ session('success') }}
            </p>
        </div>
    @endif

    {{-- POPUP: Berhasil menambahkan ke keranjang --}}
    <div x-show="showCartPopup"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="closePopup()"
         @keydown.escape.window="closePopup()"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40"
         style="display: none;">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6 relative"
             @click.stop>
            {{-- Close button --}}
            <button @click="closePopup()"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Header --}}
            <h3 class="text-lg font-bold text-gray-900 mb-4 pr-8">
                Berhasil menambahkan ke keranjang!
            </h3>

            {{-- Product row --}}
            <div class="flex items-center gap-4 py-3 border-t border-gray-100">
                <img :src="addedProduct?.image_url || '{{ asset('images/logo.png') }}'"
                     :alt="addedProduct?.name"
                     class="w-12 h-12 rounded-lg object-contain bg-gray-50 flex-shrink-0">
                <p class="text-sm font-medium text-gray-800 flex-1 line-clamp-2"
                   x-text="addedProduct?.name"></p>
                <a href="{{ route('cart.index') }}"
                   class="bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-red-700 transition flex-shrink-0">
                    Lihat Keranjang
                </a>
            </div>
        </div>
    </div>

    @if($relatedProducts->count() > 0)
        <div class="mt-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800">Produk Serupa</h2>
                @if($product->category)
                    <a href="{{ route('products.index', ['category' => $product->category->id]) }}"
                       class="text-sm text-blue-600 font-semibold hover:underline">
                        Lihat Semua
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach($relatedProducts as $related)
                    @php
                        $relImg = $related->images->first()
                            ? asset('storage/' . $related->images->first()->image_url)
                            : $fallbackImage;
                        $relEffective = $related->getEffectivePrice();
                        $relDiscountPct = $related->getDiscountPercentage();
                        $relHasDiscount = $relDiscountPct > 0;
                        $relPromo = $related->getBestActivePromotion();
                    @endphp

                    <a href="{{ route('products.show', $related->slug) }}"
                       class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-4 block group border border-gray-100">

                        <div class="relative">
                            <x-lazy-image :src="$relImg"
                                          :alt="$related->name"
                                          class="h-36 w-full rounded-xl mb-3"
                                          img-class="w-full h-full object-cover" />

                            @if($relHasDiscount)
                                <span class="absolute top-2 left-2 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                    -{{ $relDiscountPct }}%
                                </span>
                            @endif
                        </div>

                        @if($related->category)
                            <span class="text-[10px] font-bold text-green-600 uppercase tracking-wide">
                                {{ $related->category->name }}
                            </span>
                        @endif

                        <h3 class="font-semibold text-gray-800 text-sm mt-1 line-clamp-2 group-hover:text-red-600 transition">
                            {{ $related->name }}
                        </h3>

                        @if($related->weight)
                            <p class="text-xs text-gray-400 mb-1">{{ $related->weight }}</p>
                        @endif

                        <div class="mt-2">
                            @if($relHasDiscount)
                                <p class="text-xs text-gray-400 line-through">
                                    Rp {{ number_format($related->price, 0, ',', '.') }}
                                </p>
                                <p class="text-red-600 font-bold text-sm">
                                    Rp {{ number_format($relEffective, 0, ',', '.') }}
                                </p>
                            @else
                                <p class="text-gray-900 font-bold text-sm">
                                    Rp {{ number_format($related->price, 0, ',', '.') }}
                                </p>
                            @endif

                            @if($relPromo)
                                <span class="text-[10px] bg-red-50 text-red-600 font-medium px-1.5 py-0.5 rounded mt-1 inline-block">
                                    {{ $relPromo->name }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
</div>

{{-- Alpine.js component for product detail interactivity --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productDetail', (config) => ({
            images: config.images,
            fallback: config.fallback,
            stock: config.stock,
            unitPrice: config.price,
            activeIndex: 0,
            showFullDesc: false,
            imgLoaded: false,
            addingToCart: false,

            // Popup state
            showCartPopup: false,
            addedProduct: null,

            get currentImage() {
                return this.images.length > 0
                    ? this.images[this.activeIndex]
                    : this.fallback;
            },

            selectImage(index) {
                this.activeIndex = index;
            },

            async addToCart() {
                if (this.addingToCart) return;
                this.addingToCart = true;
                try {
                    const res = await fetch('{{ route("cart.add") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            product_id: {{ $product->id }},
                            quantity: 1,
                        }),
                    });

                    // 401/419 = sesi expired, arahkan ke login
                    if (res.status === 401 || res.status === 419) {
                        window.location.href = '{{ route("login") }}';
                        return;
                    }

                    const data = await res.json();

                    if (data.success) {
                        this.addedProduct = data.product;
                        this.showCartPopup = true;

                        // Update badge jumlah item di navbar
                        const badge = document.getElementById('cart-count-badge');
                        if (badge) {
                            badge.textContent = data.cart_count;
                            badge.classList.remove('hidden');
                        }
                    } else if (data.message) {
                        alert(data.message);
                    }
                } catch (e) {
                    console.error('Add to cart failed:', e);
                } finally {
                    this.addingToCart = false;
                }
            },

            closePopup() {
                this.showCartPopup = false;
                this.addedProduct = null;
            }
        }));
    });
</script>

@endsection
