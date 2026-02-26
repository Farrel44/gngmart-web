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

            <!-- Nama -->
            <div class="mb-4">
                <label class="block text-sm mb-1">
                    Nama
                </label>
                <input type="text" name="name"
                    class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
                    required>
            </div>

            <!-- No Telepon -->
            <div class="mb-4">
                <label class="block text-sm mb-1">
                    No. Telepon
                </label>
                <input type="text" name="phone"
                    class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
                    required>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-sm mb-1">
                    Email
                </label>
                <input type="email" name="email"
                    class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
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
