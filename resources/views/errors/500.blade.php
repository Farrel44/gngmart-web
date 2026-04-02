@extends('errors.layout')

@section('title', '500 — Terjadi Kesalahan')

@section('content')
<div class="text-center max-w-lg mx-auto">

    {{-- Illustration --}}
    <div class="mb-8">
        <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-red-50">
            <svg class="w-16 h-16 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>
    </div>

    {{-- Error code --}}
    <p class="text-7xl font-bold text-red-600 mb-4">500</p>

    {{-- Title --}}
    <h1 class="text-2xl font-bold text-gray-900 mb-3">Terjadi Kesalahan Server</h1>

    {{-- Description --}}
    <p class="text-gray-500 mb-8 leading-relaxed">
        Maaf, terjadi kesalahan pada server kami. Tim kami sedang menangani masalah ini.
        Silakan coba lagi dalam beberapa saat.
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
        <button onclick="location.reload()"
                class="inline-flex items-center gap-2 px-6 py-3 border border-red-600 text-red-600 rounded-full hover:bg-red-50 transition text-sm font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
            </svg>
            Coba Lagi
        </button>
    </div>

</div>
@endsection
