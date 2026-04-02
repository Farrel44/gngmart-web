@extends('errors.layout')

@section('title', '404 — Halaman Tidak Ditemukan')

@section('content')
<div class="text-center max-w-lg mx-auto">

    {{-- Illustration --}}
    <div class="mb-8">
        <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-red-50">
            <svg class="w-16 h-16 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
            </svg>
        </div>
    </div>

    {{-- Error code --}}
    <p class="text-7xl font-bold text-red-600 mb-4">404</p>

    {{-- Title --}}
    <h1 class="text-2xl font-bold text-gray-900 mb-3">Halaman Tidak Ditemukan</h1>

    {{-- Description --}}
    <p class="text-gray-500 mb-8 leading-relaxed">
        Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.
        Silakan kembali ke beranda atau cari produk yang Anda inginkan.
    </p>

    {{-- CTA Buttons --}}
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ url('/') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-full hover:bg-red-700 transition text-sm font-semibold shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Kembali ke Beranda
        </a>
        <a href="{{ url('/products') }}"
           class="inline-flex items-center gap-2 px-6 py-3 border border-red-600 text-red-600 rounded-full hover:bg-red-50 transition text-sm font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016A3.001 3.001 0 0021 9.349m-18 0a2.999 2.999 0 01.615-1.84L5.25 4.5h13.5l1.635 3.01A2.999 2.999 0 0121 9.34" />
            </svg>
            Lihat Produk
        </a>
    </div>

</div>
@endsection
