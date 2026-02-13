<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GnG Mart</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 text-gray-800">

<nav class="bg-white shadow-md fixed top-0 left-0 w-full z-50">
    <div class="max-w-screen-xl mx-auto px-6">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <a href="{{ url('/') }}" class="text-2xl font-bold text-red-600">
                GnG Mart
            </a>

            <!-- Search -->
            <div class="flex-1 mx-10 hidden md:block">
                <div class="relative">
                    <input type="text"
                        placeholder="Cari produk favorit Anda..."
                        class="w-full border border-gray-300 rounded-full px-5 py-2 pr-14 text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:outline-none">

                    <button
                        class="absolute right-2 top-1/2 -translate-y-1/2 bg-red-600 text-white px-4 py-1 rounded-full hover:bg-red-700 transition">
                        🔍
                    </button>
                </div>
            </div>

            <!-- Right Menu -->
            <div class="flex items-center gap-6">

                <!-- Cart -->
                <a href="#" class="flex items-center gap-1 hover:text-red-600 transition">
                    <span class="text-xl">🛒</span>
                    <span class="text-sm font-medium">Keranjang</span>
                </a>

                <!-- Guest -->
                @guest
                    @if (!request()->routeIs('login') && !request()->routeIs('register'))
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 border border-red-600 text-red-600 rounded-full hover:bg-red-600 hover:text-white transition">
                            Masuk
                        </a>

                        <a href="{{ route('register') }}"
                           class="px-4 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition">
                            Daftar
                        </a>
                    @endif
                @endguest

                <!-- Auth -->
                @auth
                    <span class="text-sm font-medium">
                        Halo, {{ auth()->user()->name }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="px-4 py-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition">
                            Logout
                        </button>
                    </form>
                @endauth

            </div>

        </div>
    </div>
</nav>

<main class="pt-24 pb-10">
    @yield('content')
</main>

</body>
</html>
