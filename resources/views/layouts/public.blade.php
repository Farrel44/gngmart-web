{{--
    Public Layout - untuk halaman yang bisa diakses guest maupun user terautentikasi
    
    Digunakan di: Landing page, Katalog produk, Detail produk
    Fitur: Navigation dengan search, auth links, responsive
    
    Slots:
    - $title (optional): Page title
    - $meta (optional): Additional meta tags untuk SEO
    - $slot: Main content
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'GNGMart') }}</title>

    {{-- SEO Meta Tags - dapat di-override per halaman --}}
    {{ $meta ?? '' }}

    <!-- Favicon - sama dengan dashboard admin -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex flex-col">
        {{-- Header / Navigation --}}
        <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    {{-- Logo --}}
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <x-application-logo class="h-8 w-auto fill-current text-indigo-600 dark:text-indigo-400" />
                        <span class="text-xl font-bold text-gray-900 dark:text-white">GNGMart</span>
                    </a>

                    {{-- Search Bar (desktop) --}}
                    <div class="hidden md:flex flex-1 max-w-lg mx-8">
                        <form action="{{ route('products.index') }}" method="GET" class="w-full">
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       placeholder="Cari produk..."
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Navigation Links --}}
                    <nav class="flex items-center space-x-4">
                        <a href="{{ route('products.index') }}" 
                           class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors">
                            Katalog
                        </a>

                        @auth
                            {{-- Authenticated user menu --}}
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" 
                                        class="flex items-center text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors">
                                    {{ Auth::user()->name }}
                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="open" 
                                     @click.away="open = false"
                                     x-transition
                                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50">
                                    <a href="{{ route('profile.edit') }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Profil
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" 
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            {{-- Guest links --}}
                            <a href="{{ route('login') }}" 
                               class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" 
                               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Daftar
                            </a>
                        @endauth
                    </nav>
                </div>

                {{-- Mobile Search (shown below nav on mobile) --}}
                <div class="md:hidden pb-3">
                    <form action="{{ route('products.index') }}" method="GET">
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Cari produk..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-grow">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="md:flex md:justify-between">
                    <div class="mb-6 md:mb-0">
                        <a href="{{ route('home') }}" class="flex items-center space-x-2">
                            <x-application-logo class="h-8 w-auto fill-current text-indigo-600 dark:text-indigo-400" />
                            <span class="text-xl font-bold text-gray-900 dark:text-white">GNGMart</span>
                        </a>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Belanja mudah, cepat, dan terpercaya.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">
                                Navigasi
                            </h3>
                            <ul class="mt-4 space-y-2">
                                <li>
                                    <a href="{{ route('home') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        Beranda
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('products.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        Katalog Produk
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">
                                Akun
                            </h3>
                            <ul class="mt-4 space-y-2">
                                @auth
                                    <li>
                                        <a href="{{ route('profile.edit') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                            Profil
                                        </a>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ route('login') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                            Masuk
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('register') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                            Daftar
                                        </a>
                                    </li>
                                @endauth
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                        &copy; {{ date('Y') }} GNGMart. Hak cipta dilindungi.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
