<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detail Pesanan #{{ $order->id }}
            </h2>
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
            <span class="px-3 py-1 text-sm font-medium rounded-full {{ $statusColors[$order->order_status] ?? 'bg-gray-100 text-gray-800' }}">
                {{ $order->getStatusLabel() }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

            {{-- Action Buttons (Conditional) --}}
            @if ($order->order_status === 'pending' && !$order->payment)
                <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-medium text-yellow-800 dark:text-yellow-300">Menunggu Pembayaran</h4>
                            <p class="text-sm text-yellow-700 dark:text-yellow-400">Silakan selesaikan pembayaran untuk memproses pesanan Anda.</p>
                        </div>
                        <a href="{{ route('payment.create', $order) }}" 
                           class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors">
                            Bayar Sekarang
                        </a>
                    </div>
                </div>
            @endif

            @if ($order->payment && $order->payment->payment_status === 'pending')
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <h4 class="font-medium text-blue-800 dark:text-blue-300">Menunggu Verifikasi</h4>
                    <p class="text-sm text-blue-700 dark:text-blue-400">Pembayaran Anda sedang dalam proses verifikasi oleh admin. Silakan cek secara berkala.</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Order Info & Items (Left/Main) --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Order Items --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Daftar Barang</h3>
                            
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Produk</th>
                                        <th class="py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Harga</th>
                                        <th class="py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                                        <th class="py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td class="py-4">
                                                @if ($item->product)
                                                    <a href="{{ route('products.show', $item->product->slug) }}" 
                                                       class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                                        {{ $item->product->name }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-500 italic">Produk tidak tersedia</span>
                                                @endif
                                            </td>
                                            <td class="py-4 text-right text-sm">
                                                {{-- Menggunakan harga snapshot dari order item, bukan harga produk saat ini --}}
                                                Rp {{ number_format($item->price, 0, ',', '.') }}
                                            </td>
                                            <td class="py-4 text-center text-sm">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="py-4 text-right font-medium">
                                                Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-t-2 border-gray-300 dark:border-gray-600">
                                    <tr>
                                        <td colspan="3" class="py-4 text-right font-semibold">Total</td>
                                        <td class="py-4 text-right text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                            Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Shipping Address --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Alamat Pengiriman</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ $order->address_shipment }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Sidebar: Order & Payment Info --}}
                <div class="space-y-6">
                    {{-- Order Info --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Info Pesanan</h3>
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">No. Pesanan</dt>
                                    <dd class="font-medium">#{{ $order->id }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Tanggal</dt>
                                    <dd>{{ $order->order_date->format('d M Y, H:i') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$order->order_status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $order->getStatusLabel() }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Payment Info --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Info Pembayaran</h3>
                            @if ($order->payment)
                                @php
                                    $paymentColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                        'success' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                    ];
                                @endphp
                                <dl class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Metode</dt>
                                        <dd class="font-medium">{{ $order->payment->getMethodLabel() }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $paymentColors[$order->payment->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $order->payment->getStatusLabel() }}
                                            </span>
                                        </dd>
                                    </div>
                                    @if ($order->payment->payment_date)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500 dark:text-gray-400">Tanggal Bayar</dt>
                                            <dd>{{ $order->payment->payment_date->format('d M Y, H:i') }}</dd>
                                        </div>
                                    @endif
                                </dl>

                                {{-- Preview Bukti Bayar --}}
                                @if ($order->payment->payment_proof)
                                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Bukti Pembayaran:</p>
                                        <img src="{{ asset('storage/' . $order->payment->payment_proof) }}" 
                                             alt="Bukti Pembayaran"
                                             class="w-full rounded-lg border border-gray-200 dark:border-gray-700">
                                    </div>
                                @endif
                            @else
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada pembayaran</p>
                            @endif
                        </div>
                    </div>

                    {{-- Cancel Button (only for pending orders without payment) --}}
                    @if ($order->canBeCancelled())
                        <form method="POST" action="{{ route('orders.cancel', $order) }}" 
                              onsubmit="return confirm('Yakin ingin membatalkan pesanan ini? Tindakan ini tidak dapat dibatalkan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                                Batalkan Pesanan
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Back Link --}}
            <div class="mt-6 text-center">
                <a href="{{ route('orders.index') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                    &larr; Kembali ke Riwayat Pesanan
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
