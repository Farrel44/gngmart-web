@extends('layouts.app')

@section('content')

<div class="h-[calc(100vh-64px)] flex items-center justify-center bg-gray-100">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

        <h2 class="text-center text-xl font-semibold mb-2">
            Daftar
        </h2>

        <p class="text-center text-sm text-gray-600 mb-6">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-red-600 font-semibold">
                Masuk
            </a>
        </p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            @if ($errors->any())
                <div class="bg-red-50 border border-red-300 rounded-lg p-4 mb-6">
                    <p class="text-red-700 font-semibold mb-2">Terjadi kesalahan:</p>
                    <ul class="text-red-600 text-sm list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Nama -->
            <div class="mb-4">
                <label class="block text-sm mb-1">
                    Nama
                </label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full bg-gray-100 border {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
                    required>
                @if ($errors->has('name'))
                    <p class="text-red-500 text-xs mt-1">{{ $errors->first('name') }}</p>
                @endif
            </div>

            <!-- No Telepon -->
            <div class="mb-4">
                <label class="block text-sm mb-1">
                    No. Telepon
                </label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                    class="w-full bg-gray-100 border {{ $errors->has('phone') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
                    required>
                @if ($errors->has('phone'))
                    <p class="text-red-500 text-xs mt-1">{{ $errors->first('phone') }}</p>
                @endif
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-sm mb-1">
                    Email
                </label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full bg-gray-100 border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }} rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
                    required>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label class="block text-sm mb-1">
                    Password
                </label>
                <input type="password" name="password"
                    class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
                    required>
            </div>

            <!-- Konfirmasi Password -->
            <div class="mb-6">
                <label class="block text-sm mb-1">
                    Konfirmasi Password
                </label>
                <input type="password" name="password_confirmation"
                    class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
                    required>
            </div>

            <button type="submit"
                class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">
                Daftar
            </button>

        </form>

    </div>

</div>

@endsection
