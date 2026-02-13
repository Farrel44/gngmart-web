<nav class="flex justify-between items-center px-8 py-4 bg-slate-900 text-white shadow-md">

    <div class="text-xl font-bold">
        GnG Mart
    </div>

    <div class="flex gap-4 items-center">
        @auth
            <span class="font-medium">
                Halo, {{ auth()->user()->name }}
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-white">
                    Logout
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded text-white">
                Login
            </a>

            <a href="{{ route('register') }}"
               class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded text-white">
                Register
            </a>
        @endauth    
    </div>
</nav>
