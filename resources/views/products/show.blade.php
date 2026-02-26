{{--
    Product Detail Page
    
    Menampilkan detail lengkap produk:
    - Image gallery (main + thumbnails)
    - Nama, harga, deskripsi, stok
    - Breadcrumb navigation
    - Related products dari kategori yang sama
    
    SEO: Meta tags di-set dari data produk (meta_title, meta_description, meta_keywords)
--}}

<x-layouts.public>
    {{-- Dynamic page title dengan fallback ke nama produk --}}
    <x-slot name="title">
        {{ $product->meta_title ?: $product->name }} | GNGMart
    </x-slot>

    {{-- SEO Meta Tags --}}
    <x-slot name="meta">
        @if($product->meta_description)
            <meta name="description" content="{{ $product->meta_description }}">
        @else
            <meta name="description" content="{{ Str::limit(strip_tags($product->description), 160) }}">
        @endif

        @if($product->meta_keywords)
            <meta name="keywords" content="{{ $product->meta_keywords }}">
        @endif

        {{-- Open Graph tags untuk social sharing --}}
        <meta property="og:title" content="{{ $product->meta_title ?: $product->name }}">
        <meta property="og:description" content="{{ $product->meta_description ?: Str::limit(strip_tags($product->description), 160) }}">
        <meta property="og:type" content="product">
        <meta property="og:url" content="{{ route('products.show', $product) }}">
        @if($product->images->isNotEmpty())
            <meta property="og:image" content="{{ asset('storage/' . $product->images->first()->image_path) }}">
        @endif
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb Navigation --}}
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm">
                <li>
                    <a href="{{ route('home') }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                        Beranda
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li>
                    <a href="{{ route('products.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                        Katalog
                    </a>
                </li>
                @if($product->category)
                    <li class="text-gray-400">/</li>
                    <li>
                        <a href="{{ route('products.index', ['category' => $product->category->id]) }}" 
                           class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                            {{ $product->category->name }}
                        </a>
                    </li>
                @endif
                <li class="text-gray-400">/</li>
                <li class="text-gray-900 dark:text-white font-medium truncate max-w-xs">
                    {{ $product->name }}
                </li>
            </ol>
        </nav>

        {{-- Product Detail Section --}}
        <div class="lg:grid lg:grid-cols-2 lg:gap-12">
            {{-- Image Gallery --}}
            <div x-data="{ activeImage: 0 }">
                {{-- Main Image --}}
                <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden mb-4">
                    @if($product->images->isNotEmpty())
                        @foreach($product->images as $index => $image)
                            <img x-show="activeImage === {{ $index }}"
                                 src="{{ asset('storage/' . $image->image_path) }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @endforeach
                    @else
                        {{-- No image placeholder --}}
                        <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Thumbnail Gallery (jika ada lebih dari 1 gambar) --}}
                @if($product->images->count() > 1)
                    <div class="grid grid-cols-5 gap-2">
                        @foreach($product->images as $index => $image)
                            <button @click="activeImage = {{ $index }}"
                                    :class="activeImage === {{ $index }} ? 'ring-2 ring-indigo-500' : 'ring-1 ring-gray-200 dark:ring-gray-700'"
                                    class="aspect-square rounded-md overflow-hidden focus:outline-none">
                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Product Info --}}
            <div class="mt-8 lg:mt-0">
                {{-- Category badge --}}
                @if($product->category)
                    <a href="{{ route('products.index', ['category' => $product->category->id]) }}"
                       class="inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium mb-2">
                        {{ $product->category->name }}
                    </a>
                @endif

                {{-- Product Name --}}
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $product->name }}
                </h1>

                {{-- Price Section dengan dukungan diskon --}}
                <div class="mt-4">
                    @if($product->hasDiscount())
                        {{-- Ada diskon: tampilkan harga coret + harga diskon + badge --}}
                        <div class="flex items-center gap-3">
                            <span class="text-3xl font-bold text-red-600 dark:text-red-400">
                                Rp {{ number_format($product->discount_price, 0, ',', '.') }}
                            </span>
                            <span class="text-xl text-gray-500 dark:text-gray-400 line-through">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded text-sm font-bold bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300">
                                Hemat {{ $product->getDiscountPercentage() }}%
                            </span>
                        </div>
                    @else
                        {{-- Tanpa diskon: harga normal --}}
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </p>
                    @endif
                </div>

                {{-- Stock Status --}}
                <div class="mt-4">
                    @if($product->stock > 10)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Stok Tersedia
                        </span>
                    @elseif($product->stock > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Stok Terbatas ({{ $product->stock }} tersisa)
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            Stok Habis
                        </span>
                    @endif
                </div>

                {{-- Description --}}
                @if($product->description)
                    <div class="mt-6">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-2">
                            Deskripsi
                        </h2>
                        <div class="prose prose-sm dark:prose-invert max-w-none text-gray-600 dark:text-gray-400">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </div>
                @endif

                {{-- Action buttons (placeholder untuk future Cart feature) --}}
                @if($product->stock > 0)
                    <div class="mt-8 space-y-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Fitur keranjang akan segera hadir!
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Related Products --}}
        @if($relatedProducts->isNotEmpty())
            <section class="mt-16 pt-12 border-t border-gray-200 dark:border-gray-700">
                <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    Produk Terkait
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 md:gap-6">
                    @foreach($relatedProducts as $relatedProduct)
                        <x-product-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.public>
