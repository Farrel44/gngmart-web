@extends('layouts.app')

@section('content')

<div class="max-w-6xl mx-auto px-4 py-8 pt-24">

    {{-- Flash Messages --}}
    @if(session('status') === 'profile-updated')
        <div class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-4">
            <p class="text-sm text-green-700 font-medium">Profil berhasil diperbarui.</p>
        </div>
    @endif
    @if(session('status') === 'password-updated')
        <div class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-4">
            <p class="text-sm text-green-700 font-medium">Password berhasil diubah.</p>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4">
            <ul class="text-sm text-red-700 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        {{-- Sidebar Menu --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                {{-- User Info Header --}}
                <div class="p-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        @if(auth()->user()->profile_photo)
                            <img src="{{ Storage::url(auth()->user()->profile_photo) }}"
                                 alt="{{ auth()->user()->name }}"
                                 class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 text-sm truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>

                {{-- Menu Items --}}
                <nav class="py-1">
                    <a href="{{ route('orders.index') }}"
                       class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-50 transition font-medium text-sm">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Transaksi
                    </a>

                    <a href="{{ route('profile.show') }}"
                       class="flex items-center gap-3 px-4 py-3 bg-red-50 text-red-600 transition font-medium text-sm border-l-4 border-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Akun Saya
                    </a>
                </nav>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="md:col-span-3 space-y-6">

            {{-- CARD 1: Avatar & Photo --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-6">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ Storage::url(auth()->user()->profile_photo) }}"
                             alt="{{ auth()->user()->name }}"
                             class="w-24 h-24 rounded-full object-cover flex-shrink-0 border-2 border-gray-200">
                    @else
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center text-white font-bold text-4xl flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif

                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ auth()->user()->name }}</h2>
                        <p class="text-sm text-gray-500 mb-3">{{ auth()->user()->email }}</p>
                        <button type="button"
                                class="px-4 py-2 border border-red-600 text-red-600 rounded-xl hover:bg-red-50 transition font-medium text-sm"
                                onclick="document.getElementById('photo-input').click()">
                            Ubah Foto Profil
                        </button>
                    </div>
                </div>
            </div>

            {{-- CARD 2: Data Akun (Form) --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Data Akun
                </h2>

                <form id="profile-form" method="POST" action="{{ route('profile.store') }}" class="space-y-5" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    {{-- Hidden File Input --}}
                    <input type="file" id="photo-input" name="profile_photo" accept="image/*" class="hidden">

                    {{-- Nama --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-start">
                        <label for="name" class="font-semibold text-gray-700 text-sm pt-2">Nama</label>
                        <div class="md:col-span-3">
                            <input type="text" id="name" name="name"
                                   value="{{ old('name', auth()->user()->name) }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('name')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Nomor HP --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-start">
                        <label for="phone" class="font-semibold text-gray-700 text-sm pt-2">Nomor Handphone</label>
                        <div class="md:col-span-3">
                            <input type="text" id="phone" name="phone"
                                   value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                   placeholder="Contoh: 08123456789"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('phone')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-start">
                        <label for="email" class="font-semibold text-gray-700 text-sm pt-2">Email</label>
                        <div class="md:col-span-3">
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', auth()->user()->email) }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('email')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Alamat --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-start">
                        <label for="address" class="font-semibold text-gray-700 text-sm pt-2">Alamat</label>
                        <div class="md:col-span-3">
                            <textarea id="address" name="address"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Masukkan alamat lengkap (jalan, RT/RW, kelurahan, kecamatan, kota, kode pos)..."
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none">{{ old('address', auth()->user()->address ?? '') }}</textarea>
                            <p class="text-xs text-gray-400 mt-1">Alamat ini akan otomatis terisi saat checkout.</p>
                            @error('address')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </form>

                {{-- Save Button --}}
                <div class="mt-6 flex gap-3 justify-end border-t border-gray-100 pt-6">
                    <a href="{{ route('home') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-medium text-sm">
                        Batal
                    </a>
                    <button form="profile-form" type="submit" class="px-6 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition font-medium text-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </div>

            {{-- CARD 3: Keamanan --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Keamanan Akun
                </h2>
                <p class="text-sm text-gray-500 mb-4">Ubah password akun Anda secara berkala untuk menjaga keamanan.</p>
                <button type="button"
                        onclick="document.getElementById('password-modal').classList.remove('hidden')"
                        class="px-4 py-2.5 border border-red-600 text-red-600 rounded-xl hover:bg-red-50 transition font-medium text-sm">
                    Ubah Password
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Password Change Modal --}}
<div id="password-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-lg max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Ubah Password
            </h2>
        </div>

        <form method="POST" action="{{ route('profile.password.update') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password Saat Ini</label>
                <input type="password" name="current_password"
                       placeholder="Masukkan password saat ini"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                       required>
                @error('current_password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password Baru</label>
                <input type="password" name="password"
                       placeholder="Minimal 8 karakter"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                       required>
                @error('password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation"
                       placeholder="Ulangi password baru"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                       required>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button"
                        onclick="document.getElementById('password-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-medium text-sm">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition font-medium text-sm">
                    Ubah Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('password-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });

    document.getElementById('photo-input').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            document.getElementById('profile-form').submit();
        }
    });
</script>
@endsection
