@extends('layouts.app')

@section('content')

<div class="bg-white min-h-screen">
<div class="max-w-screen-xl mx-auto px-6 pt-24 pb-12">

    {{-- Breadcrumb --}}
    <nav class="mb-6 bg-white border border-gray-200 rounded-xl px-5 py-3 text-sm text-gray-500 flex items-center gap-2 flex-wrap shadow-sm">
        <a href="{{ route('home') }}" class="hover:text-red-600 transition font-medium">Beranda</a>
        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
        @if($currentCategory)
            <a href="{{ route('products.index') }}" class="hover:text-red-600 transition font-medium">Katalog Produk</a>
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
            <span class="text-gray-800 font-semibold">{{ $currentCategory->name }}</span>
        @elseif($search)
            <a href="{{ route('products.index') }}" class="hover:text-red-600 transition font-medium">Katalog Produk</a>
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
            <span class="text-gray-800 font-semibold">Pencarian</span>
        @else
            <span class="text-gray-800 font-semibold">Katalog Produk</span>
        @endif
    </nav>

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            @if($currentCategory)
                {{ $currentCategory->name }}
            @elseif($search)
                Hasil Pencarian
            @else
                Katalog Produk
            @endif
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            @if($search)
                Menampilkan hasil untuk "<span class="font-medium text-gray-700">{{ $search }}</span>"
                @if($products->total() > 0)
                    — {{ $products->total() }} produk ditemukan
                @endif
            @else
                Temukan kebutuhan harian Anda
            @endif
        </p>
    </div>

    <div class="lg:grid lg:grid-cols-4 lg:gap-8">
        {{-- Sidebar: Category Filter --}}
        <aside class="hidden lg:block">
            <div class="sticky top-24 space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
                        Kategori
                    </h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('products.index', array_filter(['search' => $search, 'sort' => $sort])) }}"
                               class="block px-3 py-2 text-sm rounded-lg transition-colors {{ !$currentCategory ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:text-red-600 hover:bg-red-50/50' }}">
                                Semua Kategori
                            </a>
                        </li>
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ route('products.index', array_filter(['category' => $category->id, 'search' => $search, 'sort' => $sort])) }}"
                                   class="block px-3 py-2 text-sm rounded-lg transition-colors {{ $currentCategory?->id === $category->id ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:text-red-600 hover:bg-red-50/50' }}">
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
                            class="block w-full pl-3 pr-10 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
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
                    <label for="sort" class="text-sm text-gray-500 whitespace-nowrap">
                        Urutkan:
                    </label>
                    <select id="sort"
                            onchange="window.location.href = this.value"
                            class="block pl-3 pr-10 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
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
                    <span class="text-sm text-gray-500">Filter aktif:</span>

                    @if($currentCategory)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-50 text-red-600 font-medium">
                            {{ $currentCategory->name }}
                            <a href="{{ route('products.index', array_filter(['search' => $search, 'sort' => $sort])) }}"
                               class="ml-2 text-red-400 hover:text-red-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </span>
                    @endif

                    @if($search)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-700">
                            "{{ $search }}"
                            <a href="{{ route('products.index', array_filter(['category' => $currentCategory?->id, 'sort' => $sort])) }}"
                               class="ml-2 text-gray-400 hover:text-gray-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </span>
                    @endif

                    <a href="{{ route('products.index') }}"
                       class="text-sm text-gray-500 hover:text-red-600 underline">
                        Reset semua
                    </a>
                </div>
            @endif

            {{-- Products Grid --}}
            @if($products->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
                    <div class="mx-auto w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="h-10 w-10 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        Produk tidak ditemukan
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        @if($search)
                            Coba ubah kata kunci pencarian atau reset filter.
                        @else
                            Coba pilih kategori lain atau reset filter.
                        @endif
                    </p>
                    <a href="{{ route('home') }}"
                       class="mt-6 inline-block bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
                        Kembali ke Beranda
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4 md:gap-6">
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
</div>

@endsection
