@extends('layouts.app')

@section('content')

<div class="min-h-[60vh] flex items-center justify-center bg-white">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-7">

        <h2 class="text-center text-xl font-semibold mb-1">
            Verifikasi Email
        </h2>

        <p class="text-center text-sm text-gray-500 mb-5">
            Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda
            dengan mengklik link yang baru saja kami kirim. Jika Anda tidak menerima email tersebut,
            kami akan mengirimkan ulang.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                <p class="text-green-700 text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Link verifikasi baru telah dikirim ke alamat email Anda.
                </p>
            </div>
        @endif

        <div class="flex items-center justify-between gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="bg-red-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-red-700 transition text-sm">
                    Kirim Ulang Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="text-sm text-gray-500 hover:text-gray-700 underline transition">
                    Keluar
                </button>
            </form>
        </div>

    </div>

</div>

@endsection
