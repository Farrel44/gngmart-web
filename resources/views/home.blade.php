{{--
    Landing Page / Home
    
    Entry point untuk pengunjung. Menampilkan:
    - Hero section dengan CTA
    - Grid kategori untuk quick access
    - Produk unggulan (terbaru dengan stok tersedia)
--}}

<x-layouts.public>
    <x-slot name="title">GNGMart - Belanja Online Terpercaya</x-slot>

    {{-- Hero Section --}}
    <section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold tracking-tight">
                    Selamat Datang di GNGMart
                </h1>
                <p class="mt-4 text-lg md:text-xl text-indigo-100 max-w-2xl mx-auto">
                    Temukan berbagai produk berkualitas dengan harga terbaik. 
                    Belanja mudah, cepat, dan terpercaya.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-indigo-700 bg-white hover:bg-indigo-50 transition-colors">
                        Lihat Katalog
                        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                    @guest
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 border-2 border-white text-base font-medium rounded-lg text-white hover:bg-white hover:text-indigo-700 transition-colors">
                            Daftar Sekarang
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- Category Section --}}
    @if($categories->isNotEmpty())
        <section class="py-12 md:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                        Belanja Berdasarkan Kategori
                    </h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Pilih kategori untuk menemukan produk yang Anda cari
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($categories as $category)
                        <a href="{{ route('products.index', ['category' => $category->id]) }}" 
                           class="group flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            {{-- Category icon placeholder --}}
                            <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center mb-3 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800 transition-colors">
                                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white text-center group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ $category->name }}
                            </h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $category->products_count }} produk
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Featured Products Section --}}
    @if($featuredProducts->isNotEmpty())
        <section class="py-12 md:py-16 bg-white dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-10">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                            Produk Terbaru
                        </h2>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Produk-produk terbaru yang tersedia di toko kami
                        </p>
                    </div>
                    <a href="{{ route('products.index') }}" 
                       class="hidden sm:inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                        Lihat Semua
                        <svg class="ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                {{-- Product Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 md:gap-6">
                    @foreach($featuredProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                {{-- Mobile: Lihat Semua link --}}
                <div class="mt-8 text-center sm:hidden">
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">
                        Lihat Semua Produk
                        <svg class="ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- CTA Section --}}
    @guest
        <section class="py-12 md:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-indigo-700 rounded-2xl px-6 py-12 md:px-12 md:py-16 text-center">
                    <h2 class="text-2xl md:text-3xl font-bold text-white">
                        Siap Mulai Berbelanja?
                    </h2>
                    <p class="mt-4 text-indigo-100 max-w-xl mx-auto">
                        Daftar sekarang dan nikmati kemudahan berbelanja di GNGMart. 
                        Gratis dan tanpa ribet!
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-indigo-700 bg-white hover:bg-indigo-50 transition-colors">
                            Daftar Gratis
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endguest
</x-layouts.public>
