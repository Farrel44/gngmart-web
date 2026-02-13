@extends('layouts.app')

@section('content')

<div class="h-[calc(100vh-64px)] flex items-center justify-center bg-gray-100">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

        <h2 class="text-center text-xl font-semibold mb-2">
            Masuk
        </h2>

        <p class="text-center text-sm text-gray-600 mb-6">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-red-600 font-semibold">
                Daftar
            </a>
        </p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm mb-1">
                    No. Handphone/Email
                </label>
                <input type="text" name="email"
                    class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
                    required>
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">
                    Password
                </label>
                <input type="password" name="password"
                    class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
                    required>
            </div>

            <div class="flex items-center mb-6">
                <input type="checkbox" name="remember" class="mr-2">
                <span class="text-sm text-gray-600">Ingat saya</span>
            </div>

            <button type="submit"
                class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">
                Masuk
            </button>

        </form>

    </div>

</div>

@endsection
