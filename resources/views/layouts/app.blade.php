<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GnG Mart</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- CSS + JS (Alpine.js loaded via app.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white text-gray-800 font-sans antialiased min-h-screen flex flex-col">

{{--
    Navbar - Responsive navigation bar
    Logo diperbesar, search button berbentuk lingkaran, keranjang hanya untuk user login
--}}
<nav class="bg-white shadow-ld fixed top-0 left-0 w-full z-50">
    <div class="max-w-screen-xl mx-auto px-6">
        <div class="flex items-center justify-between py-4">
            <!-- Logo (diperbesar agar proporsional dengan navbar) -->
            <a href="{{ url('/') }}" class="flex-shrink-0 flex items-center">
                <img src="{{ asset('images/logo.png') }}"
                     alt="GnG Mart"
                     class="h-12 w-auto object-contain">
            </a>

            <!-- Search Bar with live suggestions (desktop only) -->
            <div class="flex-1 mx-10 hidden md:block"
                 x-data="liveSearch()"
                 @click.away="open = false"
                 @keydown.escape.window="open = false">

                <form @submit.prevent="goToResults()" class="w-full">
                    <div class="relative">
                        <input type="text"
                               x-model="query"
                               @input.debounce.300ms="fetchSuggestions()"
                               @focus="handleFocus()"
                               @keydown.arrow-down.prevent="highlightNext()"
                               @keydown.arrow-up.prevent="highlightPrev()"
                               @keydown.enter.prevent="handleEnter()"
                               placeholder="Cari produk favorit Anda..."
                               autocomplete="off"
                               class="w-full border border-gray-300 rounded-full px-5 py-2.5 pr-14 text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:outline-none">

                        <button type="submit"
                                class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-red-600 text-white w-9 h-9 rounded-full flex items-center justify-center hover:bg-red-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>

                        {{-- Dropdown panel suggestions --}}
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden"
                             style="display: none;">

                            {{-- Loading state --}}
                            <template x-if="loading">
                                <div class="px-4 py-6 text-center text-sm text-gray-400">
                                    <svg class="w-5 h-5 mx-auto mb-2 animate-spin text-gray-300" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Mencari...
                                </div>
                            </template>

                            {{-- Popular / Recommendation header --}}
                            <template x-if="!loading && showingPopular && items.length > 0">
                                <div class="px-4 pt-3 pb-1">
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Populer</p>
                                </div>
                            </template>

                            {{-- Suggestion items (text-only, Tokopedia style) --}}
                            <template x-if="!loading && items.length > 0">
                                <ul class="py-1 max-h-80 overflow-y-auto">
                                    <template x-for="(name, index) in items" :key="index">
                                        <li>
                                            <button type="button"
                                                    @click="selectSuggestion(name)"
                                                    @mouseenter="highlighted = index"
                                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-gray-50 transition"
                                                    :class="{ 'bg-gray-50': highlighted === index }">
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                </svg>
                                                <span class="text-sm text-gray-700 truncate" x-html="highlightMatch(name)"></span>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </template>

                            {{-- Empty state --}}
                            <template x-if="!loading && items.length === 0 && searched">
                                <div class="px-4 py-6 text-center">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                    </svg>
                                    <p class="text-sm text-gray-500">Tidak ada produk ditemukan</p>
                                </div>
                            </template>

                        </div>
                    </div>
                </form>
            </div>

            <!-- Right Menu -->
            <div class="flex items-center gap-6">

                {{-- Keranjang: icon di atas, teks di bawah (hanya untuk user login) --}}
                @auth
                    <a href="{{ route('cart.index') }}" class="flex flex-col items-center hover:text-red-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        <span class="text-xs font-medium mt-0.5">Keranjang</span>
                    </a>
                @endauth

                <!-- Guest: tombol Masuk & Daftar (disembunyikan di halaman auth) -->
                @guest
                    @if (!request()->routeIs('login') && !request()->routeIs('register'))
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 border border-red-600 text-red-600 rounded-full hover:bg-red-600 hover:text-white transition text-sm font-medium">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}"
                           class="px-4 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition text-sm font-medium">
                            Daftar
                        </a>
                    @endif
                @endguest

                {{-- Akun dropdown: icon + nama user, hover untuk navigasi dan logout --}}
                @auth
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button @click="open = !open"
                                class="flex flex-col items-center hover:text-red-600 transition focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-xs font-medium mt-0.5 max-w-[80px] truncate">{{ auth()->user()->name ? explode(' ', auth()->user()->name)[0] : '' }}</span>
                        </button>

                        <!-- Dropdown menu -->
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50"
                             style="display: none;">

                            <a href="{{ route('dashboard') }}"
                               class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                Dashboard
                            </a>

                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Profil Saya
                            </a>

                            <a href="{{ route('orders.index') }}"
                               class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Pesanan Saya
                            </a>

                            <div class="border-t border-gray-100 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition text-left">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth

            </div>

        </div>
    </div>
</nav>

<main class="pt-12 pb-10">
    @yield('content')
</main>

{{--
    Footer - Logo, kategori produk, bantuan, kontak, dan copyright
    Ditampilkan di semua halaman yang menggunakan layout app
--}}
<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="max-w-screen-xl mx-auto px-6 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

            {{-- Kolom 1: Logo & deskripsi --}}
            <div>
                <a href="{{ url('/') }}" class="inline-block mb-3">
                    <img src="{{ asset('images/logo.png') }}" alt="GnG Mart" class="h-14 w-auto object-contain">
                </a>
                <p class="text-sm text-gray-500 leading-relaxed">
                    GnG Mart adalah toko online terpercaya yang menyediakan berbagai kebutuhan harian dengan harga terjangkau.
                </p>
            </div>

            {{-- Kolom 2: Kategori produk --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-800 mb-3">Kategori</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('products.index') }}?category=makanan" class="text-sm text-gray-500 hover:text-red-600 transition">Makanan dan Minuman</a></li>
                    <li><a href="{{ route('products.index') }}?category=kebutuhan-rumah-tangga" class="text-sm text-gray-500 hover:text-red-600 transition">Kebutuhan Rumah Tangga</a></li>
                    <li><a href="{{ route('products.index') }}?category=aksesoris" class="text-sm text-gray-500 hover:text-red-600 transition">Aksesoris</a></li>
                    <li><a href="{{ route('products.index') }}?category=alat-tulis" class="text-sm text-gray-500 hover:text-red-600 transition">Alat Tulis</a></li>
                    <li><a href="{{ route('products.index') }}?category=kesehatan" class="text-sm text-gray-500 hover:text-red-600 transition">Kesehatan</a></li>
                    <li><a href="{{ route('products.index') }}?category=kecantikan" class="text-sm text-gray-500 hover:text-red-600 transition">Kecantikan</a></li>
                </ul>
            </div>

            {{-- Kolom 3: Bantuan --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-800 mb-3">Bantuan</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-sm text-gray-500 hover:text-red-600 transition">Cara Belanja</a></li>
                    <li><a href="#" class="text-sm text-gray-500 hover:text-red-600 transition">Tentang GnG Mart</a></li>
                    <li><a href="#" class="text-sm text-gray-500 hover:text-red-600 transition">Pengiriman</a></li>
                    <li><a href="#" class="text-sm text-gray-500 hover:text-red-600 transition">FAQ</a></li>
                </ul>
            </div>

            {{-- Kolom 4: Kontak --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-800 mb-3">Kontak</h4>
                <ul class="space-y-2">
                    <li class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        support@gngmart.com
                    </li>
                    <li class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        (021) 123-4567
                    </li>
                    <li class="flex items-start gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Jl. Contoh No. 123, Jakarta
                    </li>
                </ul>
            </div>

        </div>

        {{-- Copyright --}}
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-400 text-center">
                &copy; {{ date('Y') }} GnG Mart. All rights reserved.
            </p>
        </div>
    </div>
</footer>

{{-- Live search Alpine.js component --}}
<script>
    function liveSearch() {
        return {
            query: '',
            items: [],
            popular: [],
            open: false,
            loading: false,
            searched: false,
            showingPopular: false,
            highlighted: -1,
            popularLoaded: false,

            async handleFocus() {
                if (this.query.trim().length >= 2 && this.items.length > 0) {
                    this.open = true;
                    return;
                }
                if (this.query.trim().length < 2) {
                    await this.fetchPopular();
                }
            },

            async fetchPopular() {
                if (this.popularLoaded && this.popular.length > 0) {
                    this.items = this.popular;
                    this.showingPopular = true;
                    this.open = true;
                    return;
                }

                this.loading = true;
                this.open = true;

                try {
                    const res = await fetch(`{{ route('search.popular') }}`);
                    this.popular = await res.json();
                    this.items = this.popular;
                    this.showingPopular = true;
                    this.popularLoaded = true;
                    this.highlighted = -1;
                } catch (e) {
                    this.items = [];
                } finally {
                    this.loading = false;
                }
            },

            async fetchSuggestions() {
                const q = this.query.trim();
                if (q.length < 2) {
                    if (q.length === 0 && this.popularLoaded) {
                        this.items = this.popular;
                        this.showingPopular = true;
                        this.open = true;
                    } else {
                        this.items = [];
                        this.open = false;
                    }
                    this.searched = false;
                    return;
                }

                this.loading = true;
                this.open = true;
                this.showingPopular = false;

                try {
                    const res = await fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(q)}`);
                    this.items = await res.json();
                    this.searched = true;
                    this.highlighted = -1;
                } catch (e) {
                    this.items = [];
                } finally {
                    this.loading = false;
                }
            },

            selectSuggestion(name) {
                this.query = name;
                this.open = false;
                this.goToResults();
            },

            goToResults() {
                if (this.query.trim().length > 0) {
                    window.location.href = `{{ route('search.results') }}?q=${encodeURIComponent(this.query.trim())}`;
                }
            },

            handleEnter() {
                if (this.highlighted >= 0 && this.highlighted < this.items.length) {
                    this.selectSuggestion(this.items[this.highlighted]);
                } else {
                    this.goToResults();
                }
            },

            highlightNext() {
                if (this.highlighted < this.items.length - 1) this.highlighted++;
            },

            highlightPrev() {
                if (this.highlighted > 0) this.highlighted--;
                else this.highlighted = -1;
            },

            highlightMatch(name) {
                if (!this.query.trim() || this.showingPopular) return name;
                const escaped = this.query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regex = new RegExp(`(${escaped})`, 'gi');
                return name.replace(regex, '<span class="text-red-600 font-bold">$1</span>');
            }
        };
    }
</script>

</body>
</html>
