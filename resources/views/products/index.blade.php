{{--
    Product Catalog Page
    
    Menampilkan daftar produk dengan fitur:
    - Search (via query param ?search=)
    - Filter kategori (via ?category=)
    - Sorting (via ?sort=)
    - Pagination
    
    Semua filter mempertahankan query string saat navigasi pagination.
--}}

<x-layouts.public>
    <x-slot name="title">
        @if($currentCategory)
            {{ $currentCategory->name }} - Katalog Produk | GNGMart
        @elseif($search)
            Pencarian "{{ $search }}" | GNGMart
        @else
            Katalog Produk | GNGMart
        @endif
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                @if($currentCategory)
                    {{ $currentCategory->name }}
                @elseif($search)
                    Hasil Pencarian
                @else
                    Katalog Produk
                @endif
            </h1>

            @if($search)
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Menampilkan hasil untuk "<span class="font-medium">{{ $search }}</span>"
                    @if($products->total() > 0)
                        - {{ $products->total() }} produk ditemukan
                    @endif
                </p>
            @endif
        </div>

        <div class="lg:grid lg:grid-cols-4 lg:gap-8">
            {{-- Sidebar: Filters --}}
            <aside class="hidden lg:block">
                <div class="sticky top-24 space-y-6">
                    {{-- Category Filter --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                            Kategori
                        </h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('products.index', array_filter(['search' => $search, 'sort' => $sort])) }}" 
                                   class="block text-sm {{ !$currentCategory ? 'text-indigo-600 dark:text-indigo-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                                    Semua Kategori
                                </a>
                            </li>
                            @foreach($categories as $category)
                                <li>
                                    <a href="{{ route('products.index', array_filter(['category' => $category->id, 'search' => $search, 'sort' => $sort])) }}" 
                                       class="block text-sm {{ $currentCategory?->id === $category->id ? 'text-indigo-600 dark:text-indigo-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </aside>

            {{-- Main Content --}}
            <div class="lg:col-span-3">
                {{-- Mobile Filter & Sort Controls --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    {{-- Mobile Category Select --}}
                    <div class="lg:hidden">
                        <select onchange="window.location.href = this.value" 
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="{{ route('products.index', array_filter(['search' => $search, 'sort' => $sort])) }}"
                                    {{ !$currentCategory ? 'selected' : '' }}>
                                Semua Kategori
                            </option>
                            @foreach($categories as $category)
                                <option value="{{ route('products.index', array_filter(['category' => $category->id, 'search' => $search, 'sort' => $sort])) }}"
                                        {{ $currentCategory?->id === $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort Dropdown --}}
                    <div class="flex items-center space-x-2 sm:ml-auto">
                        <label for="sort" class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                            Urutkan:
                        </label>
                        <select id="sort" 
                                onchange="window.location.href = this.value"
                                class="block pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            @php
                                $sortOptions = [
                                    'latest' => 'Terbaru',
                                    'price_low' => 'Harga Terendah',
                                    'price_high' => 'Harga Tertinggi',
                                    'name' => 'Nama A-Z',
                                ];
                            @endphp
                            @foreach($sortOptions as $value => $label)
                                <option value="{{ route('products.index', array_filter(['category' => $currentCategory?->id, 'search' => $search, 'sort' => $value])) }}"
                                        {{ $sort === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Active Filters --}}
                @if($search || $currentCategory)
                    <div class="flex flex-wrap items-center gap-2 mb-6">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Filter aktif:</span>
                        
                        @if($currentCategory)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300">
                                {{ $currentCategory->name }}
                                <a href="{{ route('products.index', array_filter(['search' => $search, 'sort' => $sort])) }}" 
                                   class="ml-2 hover:text-indigo-900 dark:hover:text-indigo-100">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            </span>
                        @endif

                        @if($search)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                "{{ $search }}"
                                <a href="{{ route('products.index', array_filter(['category' => $currentCategory?->id, 'sort' => $sort])) }}" 
                                   class="ml-2 hover:text-gray-900 dark:hover:text-white">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            </span>
                        @endif

                        <a href="{{ route('products.index') }}" 
                           class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 underline">
                            Reset semua
                        </a>
                    </div>
                @endif

                {{-- Products Grid --}}
                @if($products->isEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                            Tidak ada produk ditemukan
                        </h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">
                            @if($search)
                                Coba ubah kata kunci pencarian atau reset filter.
                            @else
                                Coba pilih kategori lain atau reset filter.
                            @endif
                        </p>
                        <a href="{{ route('products.index') }}" 
                           class="mt-4 inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">
                            Lihat semua produk
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($products->hasPages())
                        <div class="mt-8">
                            {{ $products->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-layouts.public>
