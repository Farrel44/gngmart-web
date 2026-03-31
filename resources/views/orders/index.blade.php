@extends('layouts.app')

@section('content')

@php
    $statusColors = [
        'pending' => 'bg-yellow-100 text-yellow-700',
        'paid' => 'bg-blue-100 text-blue-700',
        'processing' => 'bg-purple-100 text-purple-700',
        'shipped' => 'bg-cyan-100 text-cyan-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-600',
    ];
    $paymentColors = [
        'pending' => 'bg-yellow-100 text-yellow-700',
        'success' => 'bg-green-100 text-green-700',
        'failed' => 'bg-red-100 text-red-600',
    ];
@endphp

<div class="bg-white min-h-screen">
<div class="max-w-screen-xl mx-auto px-6 pt-24 pb-12">

    {{-- Breadcrumb --}}
    <nav class="mb-6 bg-white border border-gray-200 rounded-xl px-5 py-3 text-sm text-gray-500 flex items-center gap-2 flex-wrap shadow-sm">
        <a href="{{ route('home') }}" class="hover:text-red-600 transition font-medium">Beranda</a>
        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
        <span class="text-gray-800 font-semibold">Pesanan Saya</span>
    </nav>

    {{-- Page Title --}}
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Riwayat Pesanan</h1>

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

    @if ($orders->isEmpty())
        {{-- Empty State --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm text-center py-20">
            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <h3 class="text-lg font-semibold text-gray-700">Belum ada pesanan</h3>
            <p class="text-sm text-gray-500 mt-1">Ayo mulai belanja dan buat pesanan pertamamu!</p>
            <a href="{{ route('products.index') }}"
               class="mt-6 inline-block bg-red-600 text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-red-700 transition">
                Mulai Belanja
            </a>
        </div>
    @else
        {{-- Orders Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="px-4 pb-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">
                                    No. Pesanan
                                </th>
                                <th class="px-4 pb-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">
                                    Tanggal
                                </th>
                                <th class="px-4 pb-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wide">
                                    Total
                                </th>
                                <th class="px-4 pb-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wide">
                                    Status Pesanan
                                </th>
                                <th class="px-4 pb-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wide">
                                    Status Bayar
                                </th>
                                <th class="px-4 pb-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wide">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($orders as $order)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-medium text-gray-900">#{{ $order->id }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-700">{{ $order->order_date->format('d M Y') }}</span>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $order->order_date->format('H:i') }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-medium text-gray-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$order->order_status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $order->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($order->payment)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $paymentColors[$order->payment->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $order->payment->getStatusLabel() }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                                Belum Bayar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('orders.show', $order) }}"
                                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 transition">
                                                Detail
                                            </a>

                                            {{-- Tombol Bayar hanya muncul jika pending dan belum ada payment --}}
                                            @if ($order->order_status === 'pending' && !$order->payment)
                                                <a href="{{ route('payment.create', $order) }}"
                                                   class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 transition">
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
                <div class="mt-6 pt-4 border-t border-gray-100">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    @endif

    {{-- Back to Shopping Link --}}
    <div class="mt-8 text-center">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-red-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Lanjut Belanja
        </a>
    </div>

</div>
</div>

@endsection
