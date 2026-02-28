@extends('layouts.app')

@section('content')

{{-- Login page: card centered vertically, soft shadow, compact spacing --}}
<div class="min-h-[60vh] flex items-center justify-center bg-white">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-7">

        <h2 class="text-center text-xl font-semibold mb-1">
            Masuk
        </h2>

        <p class="text-center text-sm text-gray-500 mb-5">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-red-500 font-semibold hover:text-red-600 transition">
                Daftar
            </a>
        </p>

        {{-- Error validation feedback --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                <ul class="text-red-600 text-sm list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email / Handphone -->
            <div class="mb-3">
                <label class="block text-xs text-gray-500 mb-1">
                    No. Handphone/Email
                </label>
                <input type="text" name="email" value="{{ old('email') }}"
                       class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:outline-none"
                       placeholder="Masukkan email atau no. handphone"
                       required>
            </div>

            <!-- Password dengan toggle show/hide -->
            <div class="mb-3" x-data="{ showPassword: false }">
                <label class="block text-xs text-gray-500 mb-1">
                    Password
                </label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" name="password"
                           class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:outline-none"
                           placeholder="Masukkan password"
                           required>

                    {{-- Tombol eye toggle untuk show/hide password --}}
                    <button type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                        {{-- Eye icon (tampil saat password tersembunyi) --}}
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        {{-- Eye-off icon (tampil saat password terlihat) --}}
                        <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Remember me -->
            <div class="flex items-center mb-5">
                <input type="checkbox" name="remember" id="remember"
                       class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                <label for="remember" class="ml-2 text-sm text-gray-500">Ingat saya</label>
            </div>

            <!-- Tombol Masuk - lebih tebal dan kokoh -->
            <button type="submit"
                    class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition text-sm">
                Masuk
            </button>

        </form>

    </div>

</div>

@endsection
