<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Keranjang Belanja') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($cart->items->isEmpty())
                        {{-- Empty Cart State --}}
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-4 text-lg font-medium">Keranjang Kosong</h3>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">
                                Anda belum menambahkan produk ke keranjang.
                            </p>
                            <a href="{{ route('products.index') }}" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                                Lihat Produk
                            </a>
                        </div>
                    @else
                        {{-- Cart Items Table --}}
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subtotal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($cart->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium">
                                                        <a href="{{ route('products.show', $item->product) }}" class="hover:text-blue-600">
                                                            {{ $item->product->name }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if ($item->product->hasDiscount())
                                                <span class="line-through text-gray-400">Rp {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                                <span class="text-green-600 font-semibold">Rp {{ number_format($item->product->discount_price, 0, ',', '.') }}</span>
                                            @else
                                                Rp {{ number_format($item->product->price, 0, ',', '.') }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input 
                                                    type="number" 
                                                    name="quantity" 
                                                    value="{{ $item->quantity }}" 
                                                    min="1" 
                                                    max="{{ $item->product->stock }}"
                                                    class="w-20 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                                                >
                                                <button type="submit" class="text-blue-600 hover:text-blue-900 text-sm">
                                                    Update
                                                </button>
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                            Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <form action="{{ route('cart.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus item ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Cart Summary --}}
                        <div class="mt-8 border-t pt-6 flex justify-between items-center">
                            <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Kosongkan semua item di keranjang?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                    Kosongkan Keranjang
                                </button>
                            </form>

                            <div class="text-right">
                                <p class="text-gray-500 dark:text-gray-400">Total ({{ $cart->getTotalItems() }} item)</p>
                                <p class="text-2xl font-bold">Rp {{ number_format($cart->getTotalPrice(), 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
