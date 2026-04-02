<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — GnG Mart</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-gray-800 font-sans antialiased min-h-screen flex flex-col">

    {{-- Navbar minimal --}}
    <nav class="bg-white shadow-sm fixed top-0 left-0 w-full z-50">
        <div class="max-w-screen-xl mx-auto px-6">
            <div class="flex items-center justify-between py-4">
                <a href="{{ url('/') }}" class="flex-shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="GnG Mart" class="h-12 w-auto object-contain">
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/products') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-red-600 transition">
                        Lihat Produk
                    </a>
                    <a href="{{ url('/') }}"
                       class="px-4 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition text-sm font-medium">
                        Beranda
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <main class="flex-1 flex items-center justify-center pt-24 pb-16 px-6">
        @yield('content')
    </main>

    {{-- Footer minimal --}}
    <footer class="bg-gray-50 border-t border-gray-100">
        <div class="max-w-screen-xl mx-auto px-6 py-8 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="GnG Mart" class="h-8 mx-auto mb-3 opacity-60">
            <p class="text-sm text-gray-400">&copy; {{ date('Y') }} GnG Mart. Kelompok 1, XII PPLG 3 — SMK Telkom Purwokerto.</p>
        </div>
    </footer>

</body>
</html>
