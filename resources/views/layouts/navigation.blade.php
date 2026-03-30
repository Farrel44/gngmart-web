<nav class="flex justify-between items-center px-8 py-4 bg-slate-900 text-white shadow-md">

    <div class="text-xl font-bold">
        <a href="{{ route('home') }}">GnG Mart</a>
    </div>

    <div class="flex gap-4 items-center">
        {{-- Navigation Links --}}
        <a href="{{ route('products.index') }}" class="hover:text-gray-300 transition-colors">
            Produk
        </a>

        @auth
            <a href="{{ route('cart.index') }}" class="hover:text-gray-300 transition-colors">
                Keranjang
            </a>

            <a href="{{ route('orders.index') }}" class="hover:text-gray-300 transition-colors">
                Pesanan
            </a>

            <span class="font-medium text-gray-300">|</span>

            <span class="font-medium">
                Halo, {{ auth()->user()->name }}
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-white">
                    Keluar
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded text-white">
                Masuk
            </a>

            <a href="{{ route('register') }}"
               class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded text-white">
                Daftar
            </a>
        @endauth    
    </div>
</nav>
