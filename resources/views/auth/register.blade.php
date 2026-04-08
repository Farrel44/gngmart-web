@extends('layouts.app')

@section('content')

{{-- Register page: mengikuti style login card (white inputs, soft shadow, compact spacing) --}}
<div class="min-h-[60vh] flex items-center justify-center bg-white py-8">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-7"
         x-data="registerForm()" x-cloak>

        <h2 class="text-center text-xl font-semibold mb-1">
            Daftar
        </h2>

        <p class="text-center text-sm text-gray-500 mb-5">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-red-500 font-semibold hover:text-red-600 transition">
                Masuk
            </a>
        </p>

        {{-- Server-side error feedback --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                <p class="text-red-700 font-semibold text-sm mb-1">Terjadi kesalahan:</p>
                <ul class="text-red-600 text-sm list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" @submit="handleSubmit($event)">
            @csrf

            <!-- Nama -->
            <div class="mb-3">
                <label class="block text-xs text-gray-500 mb-1">Nama</label>
                <input type="text" name="name"
                       x-model="name"
                       @blur="touched.name = true"
                       :class="fieldClass('name')"
                       class="w-full bg-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none transition-colors"
                       placeholder="Masukkan nama lengkap"
                       required>
                <p x-show="touched.name && errors.name" x-transition
                   class="text-red-500 text-xs mt-1" x-text="errors.name"></p>
            </div>

            <!-- No Telepon -->
            <div class="mb-3">
                <label class="block text-xs text-gray-500 mb-1">No. Handphone</label>
                <input type="text" name="phone"
                       x-model="phone"
                       @input="validatePhone()"
                       @blur="touched.phone = true; validatePhone()"
                       :class="phoneInputClass"
                       class="w-full bg-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none transition-colors"
                       placeholder="Contoh: 081234567890"
                       inputmode="tel"
                       required>

                {{-- +62 warning --}}
                <p x-show="phoneWarn62" x-transition
                   class="text-amber-600 text-xs mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Gunakan format 08xxxx, bukan +62
                </p>

                <p x-show="touched.phone && errors.phone && !phoneWarn62" x-transition
                   class="text-red-500 text-xs mt-1" x-text="errors.phone"></p>

                <p x-show="!touched.phone || (!errors.phone && !phoneWarn62)"
                   class="text-gray-400 text-xs mt-1">
                    Gunakan nomor HP aktif (format 08xxx, 10-13 digit)
                </p>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="block text-xs text-gray-500 mb-1">Email</label>
                <input type="email" name="email"
                       x-model="email"
                       @input="validateEmail()"
                       @blur="touched.email = true; validateEmail()"
                       :class="fieldClass('email')"
                       class="w-full bg-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none transition-colors"
                       placeholder="Masukkan alamat email"
                       required>
                <p x-show="touched.email && errors.email" x-transition
                   class="text-red-500 text-xs mt-1" x-text="errors.email"></p>
            </div>

            <!-- Password dengan toggle show/hide + strength indicator -->
            <div class="mb-3">
                <label class="block text-xs text-gray-500 mb-1">Kata Sandi</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" name="password"
                           x-model="password"
                           @input="validatePassword(); validateConfirm()"
                           @blur="touched.password = true; validatePassword()"
                           :class="fieldClass('password')"
                           class="w-full bg-white rounded-lg px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none transition-colors"
                           placeholder="Buat kata sandi (min. 8 karakter)"
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

                {{-- Password strength indicator --}}
                <div x-show="password.length > 0" x-transition class="mt-1.5">
                    <div class="flex gap-1 mb-1">
                        <div class="h-1 flex-1 rounded-full transition-colors"
                             :class="passwordStrength >= 1 ? strengthColor : 'bg-gray-200'"></div>
                        <div class="h-1 flex-1 rounded-full transition-colors"
                             :class="passwordStrength >= 2 ? strengthColor : 'bg-gray-200'"></div>
                        <div class="h-1 flex-1 rounded-full transition-colors"
                             :class="passwordStrength >= 3 ? strengthColor : 'bg-gray-200'"></div>
                        <div class="h-1 flex-1 rounded-full transition-colors"
                             :class="passwordStrength >= 4 ? strengthColor : 'bg-gray-200'"></div>
                    </div>
                    <p class="text-xs" :class="strengthTextColor" x-text="strengthLabel"></p>
                </div>

                <p x-show="touched.password && errors.password && password.length === 0" x-transition
                   class="text-red-500 text-xs mt-1" x-text="errors.password"></p>
            </div>

            <!-- Konfirmasi Password dengan toggle show/hide -->
            <div class="mb-5">
                <label class="block text-xs text-gray-500 mb-1">Konfirmasi Kata Sandi</label>
                <div class="relative">
                    <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation"
                           x-model="passwordConfirmation"
                           @input="validateConfirm()"
                           @blur="touched.passwordConfirmation = true; validateConfirm()"
                           :class="fieldClass('passwordConfirmation')"
                           class="w-full bg-white rounded-lg px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none transition-colors"
                           placeholder="Ulangi kata sandi"
                           required>
                    <button type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                        <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <p x-show="touched.passwordConfirmation && errors.passwordConfirmation" x-transition
                   class="text-red-500 text-xs mt-1" x-text="errors.passwordConfirmation"></p>

                {{-- Match confirmation --}}
                <p x-show="touched.passwordConfirmation && !errors.passwordConfirmation && passwordConfirmation.length > 0" x-transition
                   class="text-green-600 text-xs mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Kata sandi cocok
                </p>
            </div>

            <!-- Tombol Daftar -->
            <button type="submit"
                    :disabled="!isFormValid"
                    :class="isFormValid ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-300 cursor-not-allowed'"
                    class="w-full text-white py-3 rounded-lg font-semibold transition text-sm">
                Daftar
            </button>

        </form>

    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('registerForm', () => ({
        name: '{{ old("name", "") }}',
        phone: '{{ old("phone", "") }}',
        email: '{{ old("email", "") }}',
        password: '',
        passwordConfirmation: '',
        showPassword: false,
        showConfirmPassword: false,
        phoneWarn62: false,

        touched: {
            name: {{ $errors->any() ? 'true' : 'false' }},
            phone: {{ $errors->any() ? 'true' : 'false' }},
            email: {{ $errors->any() ? 'true' : 'false' }},
            password: false,
            passwordConfirmation: false,
        },

        errors: {
            name: '',
            phone: '',
            email: '',
            password: '',
            passwordConfirmation: '',
        },

        // -- Field class helper --
        fieldClass(field) {
            if (!this.touched[field]) return 'border border-gray-300 focus:border-red-500';
            if (this.errors[field]) return 'border border-red-500 focus:border-red-500';
            return 'border border-green-500 focus:border-green-500';
        },

        get phoneInputClass() {
            if (!this.touched.phone) return 'border border-gray-300 focus:border-red-500';
            if (this.phoneWarn62) return 'border border-amber-400 focus:border-amber-500';
            if (this.errors.phone) return 'border border-red-500 focus:border-red-500';
            return 'border border-green-500 focus:border-green-500';
        },

        // -- Validators --
        validateName() {
            this.errors.name = '';
            if (this.name.trim().length === 0) {
                this.errors.name = 'Nama wajib diisi';
            } else if (this.name.trim().length < 3) {
                this.errors.name = 'Nama minimal 3 karakter';
            }
        },

        validatePhone() {
            const v = this.phone.trim();
            this.phoneWarn62 = false;
            this.errors.phone = '';

            if (v.length === 0) {
                if (this.touched.phone) this.errors.phone = 'Nomor HP wajib diisi';
                return;
            }
            if (v.startsWith('+62')) {
                this.phoneWarn62 = true;
                return;
            }
            const cleaned = v.replace(/[\s\-]/g, '');
            if (!/^08\d{8,11}$/.test(cleaned)) {
                this.errors.phone = 'Nomor HP harus diawali 08 dan 10-13 digit';
            }
        },

        validateEmail() {
            this.errors.email = '';
            const v = this.email.trim();
            if (v.length === 0) {
                if (this.touched.email) this.errors.email = 'Email wajib diisi';
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
                this.errors.email = 'Format email tidak valid';
            }
        },

        validatePassword() {
            this.errors.password = '';
            if (this.password.length === 0 && this.touched.password) {
                this.errors.password = 'Kata sandi wajib diisi';
            }
        },

        validateConfirm() {
            this.errors.passwordConfirmation = '';
            if (this.passwordConfirmation.length === 0) {
                if (this.touched.passwordConfirmation) this.errors.passwordConfirmation = 'Konfirmasi kata sandi wajib diisi';
                return;
            }
            if (this.password !== this.passwordConfirmation) {
                this.errors.passwordConfirmation = 'Kata sandi tidak cocok';
            }
        },

        // -- Password strength --
        get passwordStrength() {
            let s = 0;
            if (this.password.length >= 8) s++;
            if (/[A-Z]/.test(this.password)) s++;
            if (/[0-9]/.test(this.password)) s++;
            if (/[^A-Za-z0-9]/.test(this.password)) s++;
            return s;
        },

        get strengthColor() {
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
            return colors[this.passwordStrength - 1] || 'bg-gray-200';
        },

        get strengthTextColor() {
            const colors = ['text-red-500', 'text-orange-500', 'text-yellow-600', 'text-green-600'];
            return colors[this.passwordStrength - 1] || 'text-gray-400';
        },

        get strengthLabel() {
            if (this.password.length === 0) return '';
            if (this.password.length < 8) return 'Terlalu pendek (min. 8 karakter)';
            const labels = ['Lemah', 'Cukup', 'Kuat', 'Sangat kuat'];
            return labels[this.passwordStrength - 1] || 'Lemah';
        },

        // -- Form validity --
        get isFormValid() {
            return this.name.trim().length >= 3
                && !this.errors.phone && !this.phoneWarn62 && this.phone.trim().length > 0
                && !this.errors.email && this.email.trim().length > 0
                && this.password.length >= 8
                && this.passwordConfirmation.length > 0
                && this.password === this.passwordConfirmation;
        },

        handleSubmit(e) {
            // Touch all fields and run validations
            Object.keys(this.touched).forEach(k => this.touched[k] = true);
            this.validateName();
            this.validatePhone();
            this.validateEmail();
            this.validatePassword();
            this.validateConfirm();

            const hasErrors = Object.values(this.errors).some(e => e !== '');
            if (hasErrors || this.phoneWarn62 || !this.isFormValid) {
                e.preventDefault();
                return;
            }

            // Clean phone before submit
            this.phone = this.phone.trim().replace(/[\s\-]/g, '');
        },
    }));
});
</script>

@endsection
