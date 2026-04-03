@extends('layouts.app')

@section('content')

{{-- Login page: card centered vertically, soft shadow, compact spacing --}}
<div class="min-h-[60vh] flex items-center justify-center bg-white">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-7"
         x-data="loginForm()" x-cloak>

        <h2 class="text-center text-xl font-semibold mb-1">
            Masuk
        </h2>

        <p class="text-center text-sm text-gray-500 mb-5">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-red-500 font-semibold hover:text-red-600 transition">
                Daftar
            </a>
        </p>

        {{-- Server-side error feedback --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                <ul class="text-red-600 text-sm list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" @submit="handleSubmit($event)">
            @csrf

            <!-- Email / Handphone -->
            <div class="mb-3">
                <label class="block text-xs text-gray-500 mb-1">
                    No. Handphone / Email
                </label>
                <input type="text" name="email"
                       x-model="identity"
                       @input="validateIdentity()"
                       @blur="touchedIdentity = true; validateIdentity()"
                       :class="identityInputClass"
                       class="w-full bg-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none transition-colors"
                       placeholder="Contoh: 081234567890 atau email@contoh.com"
                       autocomplete="username"
                       required>

                {{-- +62 warning --}}
                <p x-show="identityWarn62" x-transition
                   class="text-amber-600 text-xs mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Gunakan format 08xxxx, bukan +62
                </p>

                {{-- Error message --}}
                <p x-show="touchedIdentity && identityError && !identityWarn62" x-transition
                   class="text-red-500 text-xs mt-1" x-text="identityError"></p>

                {{-- Hint --}}
                <p x-show="!touchedIdentity || (!identityError && !identityWarn62)"
                   class="text-gray-400 text-xs mt-1">
                    Masukkan email atau nomor HP terdaftar
                </p>
            </div>

            <!-- Password dengan toggle show/hide -->
            <div class="mb-3">
                <label class="block text-xs text-gray-500 mb-1">
                    Kata Sandi
                </label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" name="password"
                           x-model="password"
                           @blur="touchedPassword = true"
                           :class="passwordInputClass"
                           class="w-full bg-white rounded-lg px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none transition-colors"
                           placeholder="Masukkan kata sandi"
                           autocomplete="current-password"
                           required>

                    <button type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>

                <p x-show="touchedPassword && password.length > 0 && password.length < 8" x-transition
                   class="text-red-500 text-xs mt-1">Kata sandi minimal 8 karakter</p>
            </div>

            <!-- Remember me -->
            <div class="flex items-center mb-5">
                <input type="checkbox" name="remember" id="remember"
                       class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                <label for="remember" class="ml-2 text-sm text-gray-500">Ingat saya</label>
            </div>

            <!-- Tombol Masuk -->
            <button type="submit"
                    :disabled="!isLoginValid"
                    :class="isLoginValid ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-300 cursor-not-allowed'"
                    class="w-full text-white py-3 rounded-lg font-semibold transition text-sm">
                Masuk
            </button>

        </form>

    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('loginForm', () => ({
        identity: '{{ old("email", "") }}',
        password: '',
        showPassword: false,
        touchedIdentity: {{ $errors->any() ? 'true' : 'false' }},
        touchedPassword: false,
        identityError: '',
        identityWarn62: false,

        get identityInputClass() {
            if (!this.touchedIdentity) return 'border border-gray-300 focus:border-red-500';
            if (this.identityWarn62) return 'border border-amber-400 focus:border-amber-500';
            if (this.identityError) return 'border border-red-500 focus:border-red-500';
            return 'border border-gray-300 focus:border-red-500';
        },

        get passwordInputClass() {
            if (!this.touchedPassword || this.password.length === 0) return 'border border-gray-300 focus:border-red-500';
            if (this.password.length < 8) return 'border border-red-500 focus:border-red-500';
            return 'border border-gray-300 focus:border-red-500';
        },

        get isLoginValid() {
            return this.identity.trim().length > 0 && this.password.length >= 1 && !this.identityError;
        },

        validateIdentity() {
            const v = this.identity.trim();
            this.identityWarn62 = false;
            this.identityError = '';

            if (v.length === 0) {
                if (this.touchedIdentity) this.identityError = 'Email atau nomor HP wajib diisi';
                return;
            }

            // Detect +62 prefix
            if (v.startsWith('+62')) {
                this.identityWarn62 = true;
                return;
            }

            // Phone detection: starts with 0 or pure digits
            const isPhone = /^\d+$/.test(v.replace(/[\s\-]/g, ''));
            if (isPhone || v.startsWith('0')) {
                const cleaned = v.replace(/[\s\-]/g, '');
                if (!/^08\d{8,11}$/.test(cleaned)) {
                    this.identityError = 'Nomor HP harus diawali 08 dan 10-13 digit';
                }
                return;
            }

            // Email check
            if (v.includes('@')) {
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
                    this.identityError = 'Format email tidak valid';
                }
                return;
            }

            // Not yet clear what it is — no error while typing
        },

        handleSubmit(e) {
            this.touchedIdentity = true;
            this.validateIdentity();
            if (this.identityError || this.identityWarn62) {
                e.preventDefault();
            }
            // Auto-strip spaces/dashes from phone before submit
            const cleaned = this.identity.trim().replace(/[\s\-]/g, '');
            if (/^08\d+$/.test(cleaned)) {
                this.identity = cleaned;
            }
        },
    }));
});
</script>

@endsection
