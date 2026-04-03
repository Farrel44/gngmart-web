@extends('layouts.app')

@section('content')

<div class="min-h-[60vh] flex items-center justify-center bg-white">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-7"
         x-data="forgotPasswordForm()" x-cloak>

        <h2 class="text-center text-xl font-semibold mb-1">
            Lupa Kata Sandi
        </h2>

        <p class="text-center text-sm text-gray-500 mb-5">
            Masukkan email Anda dan kami akan mengirimkan link untuk mengatur ulang kata sandi.
        </p>

        {{-- Success status --}}
        @if (session('status'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                <p class="text-green-700 text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('status') }}
                </p>
            </div>
        @endif

        {{-- Server-side errors --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                <ul class="text-red-600 text-sm list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" @submit="handleSubmit($event)">
            @csrf

            <!-- Email -->
            <div class="mb-5">
                <label class="block text-xs text-gray-500 mb-1">Email</label>
                <input type="email" name="email"
                       x-model="email"
                       @input="validateEmail()"
                       @blur="touched = true; validateEmail()"
                       :class="inputClass"
                       class="w-full bg-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none transition-colors"
                       placeholder="Masukkan alamat email terdaftar"
                       required autofocus>

                <p x-show="touched && emailError" x-transition
                   class="text-red-500 text-xs mt-1" x-text="emailError"></p>

                <p x-show="!touched || !emailError"
                   class="text-gray-400 text-xs mt-1">
                    Link reset akan dikirim ke email ini
                </p>
            </div>

            <button type="submit"
                    :disabled="!isValid"
                    :class="isValid ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-300 cursor-not-allowed'"
                    class="w-full text-white py-3 rounded-lg font-semibold transition text-sm">
                Kirim Link Reset
            </button>

            <p class="text-center text-sm text-gray-500 mt-4">
                <a href="{{ route('login') }}" class="text-red-500 font-semibold hover:text-red-600 transition">
                    &larr; Kembali ke halaman masuk
                </a>
            </p>

        </form>

    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('forgotPasswordForm', () => ({
        email: '{{ old("email", "") }}',
        touched: {{ $errors->any() ? 'true' : 'false' }},
        emailError: '',

        get inputClass() {
            if (!this.touched) return 'border border-gray-300 focus:border-red-500';
            if (this.emailError) return 'border border-red-500 focus:border-red-500';
            return 'border border-green-500 focus:border-green-500';
        },

        get isValid() {
            return this.email.trim().length > 0 && !this.emailError;
        },

        validateEmail() {
            this.emailError = '';
            const v = this.email.trim();
            if (v.length === 0) {
                if (this.touched) this.emailError = 'Email wajib diisi';
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
                this.emailError = 'Format email tidak valid';
            }
        },

        handleSubmit(e) {
            this.touched = true;
            this.validateEmail();
            if (this.emailError) e.preventDefault();
        },
    }));
});
</script>

@endsection
