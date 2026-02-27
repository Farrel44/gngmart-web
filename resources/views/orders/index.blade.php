<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Riwayat Pesanan') }}
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

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('info'))
                <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
                    {{ session('info') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($orders->isEmpty())
                        {{-- Empty State --}}
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Belum ada pesanan</h3>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">Ayo mulai belanja dan buat pesanan pertamamu!</p>
                            <a href="{{ route('products.index') }}" 
                               class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                Mulai Belanja
                            </a>
                        </div>
                    @else
                        {{-- Orders Table --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            No. Pesanan
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Tanggal
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Total
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Status Pesanan
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Status Bayar
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($orders as $order)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-4 py-4">
                                                <span class="font-medium">#{{ $order->id }}</span>
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                {{ $order->order_date->format('d M Y') }}
                                                <div class="text-xs">{{ $order->order_date->format('H:i') }}</div>
                                            </td>
                                            <td class="px-4 py-4 text-right font-medium">
                                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                        'paid' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                                        'processing' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                                        'shipped' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
                                                        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                                    ];
                                                @endphp
                                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$order->order_status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $order->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                @if ($order->payment)
                                                    @php
                                                        $paymentColors = [
                                                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                            'success' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                            'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                                        ];
                                                    @endphp
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $paymentColors[$order->payment->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                                        {{ $order->payment->getStatusLabel() }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                                        Belum Bayar
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="{{ route('orders.show', $order) }}" 
                                                       class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">
                                                        Detail
                                                    </a>
                                                    
                                                    {{-- Tombol Bayar hanya muncul jika pending dan belum ada payment --}}
                                                    @if ($order->order_status === 'pending' && !$order->payment)
                                                        <a href="{{ route('payment.create', $order) }}" 
                                                           class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 text-sm font-medium">
                                                            Bayar
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-6">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Back to Shopping Link --}}
            <div class="mt-6 text-center">
                <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                    &larr; Lanjut Belanja
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
