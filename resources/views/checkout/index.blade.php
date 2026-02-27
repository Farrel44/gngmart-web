<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Checkout') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Order Summary (Left/Main Column) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Ringkasan Pesanan</h3>
                            
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Produk</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Harga</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($cart->items as $item)
                                        <tr>
                                            <td class="px-4 py-4">
                                                <div class="text-sm font-medium">{{ $item->product->name }}</div>
                                                @if ($item->product->hasDiscount())
                                                    <span class="text-xs text-green-600">Diskon {{ $item->product->getDiscountPercentage() }}%</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 text-right text-sm">
                                                @if ($item->product->hasDiscount())
                                                    <span class="line-through text-gray-400 text-xs block">
                                                        Rp {{ number_format($item->product->price, 0, ',', '.') }}
                                                    </span>
                                                @endif
                                                <span>Rp {{ number_format($item->product->getEffectivePrice(), 0, ',', '.') }}</span>
                                            </td>
                                            <td class="px-4 py-4 text-center text-sm">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="px-4 py-4 text-right text-sm font-semibold">
                                                Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50 dark:bg-gray-700">
                                        <td colspan="3" class="px-4 py-4 text-right font-semibold">
                                            Total ({{ $cart->getTotalItems() }} item)
                                        </td>
                                        <td class="px-4 py-4 text-right text-lg font-bold text-blue-600 dark:text-blue-400">
                                            Rp {{ number_format($cart->getTotalPrice(), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="mt-4">
                                <a href="{{ route('cart.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                    &larr; Kembali ke Keranjang
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shipping Form (Right/Sidebar Column) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Alamat Pengiriman</h3>
                            
                            <form action="{{ route('checkout.store') }}" method="POST">
                                @csrf

                                <div class="mb-4">
                                    <label for="address_shipment" class="block text-sm font-medium mb-2">
                                        Alamat Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <textarea 
                                        id="address_shipment"
                                        name="address_shipment"
                                        rows="4"
                                        required
                                        minlength="10"
                                        maxlength="500"
                                        placeholder="Masukkan alamat lengkap termasuk nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, dan kode pos..."
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >{{ old('address_shipment', $user->address) }}</textarea>
                                    
                                    @error('address_shipment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Alamat otomatis terisi dari profil Anda. Silakan ubah jika perlu.
                                    </p>
                                </div>

                                {{-- Info Box --}}
                                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                                    <p class="text-sm text-blue-800 dark:text-blue-200">
                                        <strong>Info:</strong> Setelah menekan tombol di bawah, Anda akan diarahkan ke halaman pembayaran untuk memilih metode pembayaran.
                                    </p>
                                </div>

                                <button 
                                    type="submit"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-md transition duration-200"
                                >
                                    Lanjut ke Pembayaran
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- User Info Card --}}
                    <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h4 class="text-sm font-semibold mb-3 text-gray-500 uppercase">Info Pemesan</h4>
                            <dl class="space-y-2 text-sm">
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Nama</dt>
                                    <dd class="font-medium">{{ $user->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                                    <dd class="font-medium">{{ $user->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">No. Telepon</dt>
                                    <dd class="font-medium">{{ $user->phone ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
