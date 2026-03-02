@extends('layouts.app')

@section('content')

<div class="max-w-6xl mx-auto px-4 py-8 pt-24">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        {{-- Sidebar Menu --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                {{-- User Info Header --}}
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center text-white font-bold text-lg">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                </div>

                {{-- Menu Items --}}
                <nav class="space-y-1">
                    <a href="{{ route('orders.index') }}"
                       class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-50 transition font-medium text-sm">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Transaksi
                    </a>

                    <a href="{{ route('profile.show') }}"
                       class="flex items-center gap-3 px-4 py-3 bg-gray-100 text-red-600 transition font-medium text-sm border-l-4 border-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-5m-4 0V5a2 2 0 10-4 0v1m4 0a2 2 0 104 0m-5 9h2"/>
                        </svg>
                        Akun Saya
                    </a>
                </nav>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="md:col-span-3">
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Data Akun</h2>

                {{-- Avatar Section --}}
                <div class="flex items-start gap-6 pb-6 border-b border-gray-200 mb-6">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ Storage::url(auth()->user()->profile_photo) }}" 
                             alt="{{ auth()->user()->name }}"
                             class="w-32 h-32 rounded-lg object-cover flex-shrink-0 border-2 border-gray-200">
                    @else
                        <div class="w-32 h-32 rounded-lg bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center text-white font-bold text-5xl flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif

                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Data Profil</h3>
                        <button type="button" class="px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50 transition font-medium text-sm" onclick="document.getElementById('photo-input').click()">
                            Ubah Foto Profil
                        </button>
                    </div>
                </div>

                {{-- Form Data --}}
                <form id="profile-form" method="POST" action="{{ route('profile.store') }}" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    {{-- Hidden File Input --}}
                    <input type="file" id="photo-input" name="profile_photo" accept="image/*" style="display: none;">

                    {{-- Nama --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                        <label class="font-semibold text-gray-700">Nama</label>
                        <input type="text"
                               name="name"
                               value="{{ auth()->user()->name }}"
                               class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>

                    {{-- Nomor HP --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                        <label class="font-semibold text-gray-700">Nomor Handphone</label>
                        <input type="text"
                               name="phone"
                               value="{{ auth()->user()->phone ?? '' }}"
                               placeholder="Belum diisi"
                               class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>

                    {{-- Email --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                        <label class="font-semibold text-gray-700">Email</label>
                        <input type="email"
                               name="email"
                               value="{{ auth()->user()->email }}"
                               class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>
                </form>

                {{-- Save Button --}}
                <div class="mt-8 flex gap-3 justify-end">
                    <a href="{{ route('home') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                        Batal
                    </a>
                    <button form="profile-form" type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                        Simpan Perubahan
                    </button>
                </div>

                {{-- Password Change Section --}}
                <div class="mt-8 p-6 border border-gray-200 rounded-lg bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Keamanan Akun</h3>
                    <p class="text-sm text-gray-600 mb-4">Ubah password akun anda secara berkala untuk menjaga keamanan</p>
                    <button type="button" onclick="document.getElementById('password-modal').classList.remove('hidden')" class="px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50 transition font-medium text-sm">
                        Ubah Password
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Password Change Modal --}}
<div id="password-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Ubah Password</h2>
        </div>

        <form method="POST" action="{{ route('profile.password.update') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            {{-- Current Password --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Password Saat Ini</label>
                <input type="password"
                       name="current_password"
                       placeholder="Masukkan password saat ini"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                       required>
                @error('current_password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- New Password --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
                <input type="password"
                       name="password"
                       placeholder="Masukkan password baru"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                       required>
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password Baru</label>
                <input type="password"
                       name="password_confirmation"
                       placeholder="Konfirmasi password baru"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                       required>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="document.getElementById('password-modal').classList.add('hidden')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                    Ubah Password
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Close modal when clicking outside --}}
<script>
    document.getElementById('password-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });

    // Handle profile photo upload
    document.getElementById('photo-input').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            // Submit form when file is selected
            document.getElementById('profile-form').submit();
        }
    });
</script>
