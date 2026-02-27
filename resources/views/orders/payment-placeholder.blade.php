<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pilih Metode Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Order Info --}}
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h3 class="font-semibold mb-3">Pesanan #{{ $order->id }}</h3>
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="text-gray-500">Tanggal:</dt>
                            <dd>{{ $order->order_date->format('d M Y, H:i') }}</dd>
                            <dt class="text-gray-500">Status:</dt>
                            <dd>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ $order->getStatusLabel() }}
                                </span>
                            </dd>
                            <dt class="text-gray-500">Total:</dt>
                            <dd class="font-bold text-lg text-blue-600">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </dd>
                        </dl>
                    </div>

                    {{-- Placeholder Notice --}}
                    <div class="text-center py-8">
                        <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium">Fitur Pembayaran Dalam Pengembangan</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">
                            Halaman pilih metode pembayaran akan tersedia di Phase 8.
                        </p>
                        <p class="mt-1 text-sm text-gray-400">
                            Pesanan Anda sudah tersimpan dengan status "Menunggu Pembayaran".
                        </p>
                    </div>

                    {{-- Alamat Pengiriman --}}
                    <div class="mt-6 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-500 uppercase mb-2">Alamat Pengiriman</h4>
                        <p class="text-sm whitespace-pre-line">{{ $order->address_shipment }}</p>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-6 flex gap-4">
                        <a href="{{ route('home') }}" class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                            Lanjut Belanja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
