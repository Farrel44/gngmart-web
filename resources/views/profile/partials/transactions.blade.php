@php
    $statusColors = [
        'pending' => 'bg-yellow-100 text-yellow-700',
        'paid' => 'bg-blue-100 text-blue-700',
        'processing' => 'bg-purple-100 text-purple-700',
        'shipped' => 'bg-cyan-100 text-cyan-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-600',
    ];

    $statusDotColors = [
        'pending' => 'bg-yellow-500',
        'paid' => 'bg-blue-500',
        'processing' => 'bg-purple-500',
        'shipped' => 'bg-cyan-500',
        'completed' => 'bg-green-500',
        'cancelled' => 'bg-red-500',
    ];

    $currentStatus = request('status', '');
    $statusTabs = [
        '' => 'Semua',
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Sudah Dibayar',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
@endphp

{{-- Page Title --}}
<div class="mb-5">
    <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        Riwayat Pesanan
    </h1>
</div>

{{-- Flash Messages --}}
@if (session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4">
        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
    </div>
@endif
@if (session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
    </div>
@endif
@if (session('info'))
    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <p class="text-sm text-blue-700 font-medium">{{ session('info') }}</p>
    </div>
@endif

{{-- Status Filter Tabs --}}
<div class="mb-5 overflow-x-auto -mx-1 px-1 scrollbar-hide">
    <div class="flex gap-2 min-w-max">
        @foreach ($statusTabs as $value => $label)
            <a href="{{ route('profile.show', array_filter(['tab' => 'transactions', 'status' => $value])) }}"
               class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition whitespace-nowrap
                      {{ $currentStatus === $value
                          ? 'bg-red-600 text-white shadow-sm'
                          : 'bg-white border border-gray-200 text-gray-600 hover:border-red-300 hover:text-red-600' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

@if ($orders->isEmpty())
    {{-- Empty State --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm text-center py-16 px-6">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Belum ada pesanan</h3>
        <p class="text-sm text-gray-500 mt-1.5 max-w-xs mx-auto">Ayo mulai belanja dan buat pesanan pertamamu!</p>
        <a href="{{ route('products.index') }}"
           class="mt-5 inline-flex items-center gap-2 bg-red-600 text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-red-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
            </svg>
            Mulai Belanja
        </a>
    </div>
@else
    {{-- Order Cards --}}
    <div class="space-y-4">
        @foreach ($orders as $order)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden">

                {{-- Card Header --}}
                <div class="px-4 sm:px-5 pt-4 pb-3 flex flex-wrap items-center justify-between gap-2 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-gray-900">#{{ $order->id }}</span>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span class="text-xs text-gray-500">{{ $order->order_date->format('d M Y, H:i') }}</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$order->order_status] ?? 'bg-gray-100 text-gray-800' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusDotColors[$order->order_status] ?? 'bg-gray-500' }}"></span>
                        {{ $order->getStatusLabel() }}
                    </span>
                </div>

                {{-- Card Body: Product Preview --}}
                <div class="px-4 sm:px-5 py-4">
                    @php
                        $displayItems = $order->items->take(2);
                        $remainingCount = $order->items->count() - 2;
                        $totalItems = $order->items->sum('quantity');
                    @endphp

                    <div class="space-y-3">
                        @foreach ($displayItems as $item)
                            <div class="flex items-center gap-3">
                                {{-- Product Image --}}
                                @if ($item->product && $item->product->images->first())
                                    <div class="w-12 h-12 rounded-lg overflow-hidden relative bg-gray-100 flex-shrink-0 border border-gray-100">
                                        <div class="absolute inset-0 skeleton-shimmer"></div>
                                        <img src="{{ Storage::url($item->product->images->first()->image_url) }}"
                                             alt="{{ $item->product->name }}"
                                             class="w-full h-full object-cover relative z-10 transition-opacity duration-300"
                                             style="opacity:0"
                                             loading="lazy"
                                             onload="this.style.opacity='1';this.previousElementSibling.remove()">
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 border border-gray-100">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif

                                {{-- Product Info --}}
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-800 truncate">
                                        {{ $item->product->name ?? 'Produk tidak tersedia' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($remainingCount > 0)
                        <p class="mt-2 text-xs text-gray-400">+{{ $remainingCount }} produk lainnya</p>
                    @endif
                </div>

                {{-- Card Footer --}}
                <div class="px-4 sm:px-5 py-3 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    {{-- Total --}}
                    <div>
                        <p class="text-xs text-gray-500">Total Pesanan ({{ $totalItems }} produk)</p>
                        <p class="text-base font-bold text-red-600">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 sm:flex-shrink-0">
                        <a href="{{ route('orders.show', $order) }}"
                           class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition w-full sm:w-auto">
                            Detail
                        </a>

                        @if ($order->order_status === 'pending' && !$order->payment)
                            <a href="{{ route('payment.create', $order) }}"
                               class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium bg-red-600 text-white hover:bg-red-700 transition w-full sm:w-auto">
                                Bayar
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if ($orders->hasPages())
        <div class="mt-6">
            {{ $orders->appends(['tab' => 'transactions', 'status' => $currentStatus])->links() }}
        </div>
    @endif
@endif
