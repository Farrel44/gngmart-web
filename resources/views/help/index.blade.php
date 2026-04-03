@extends('layouts.app')

@section('content')
<div class="bg-white min-h-screen pt-24 pb-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Cara Berbelanja di GnG Mart</h1>
            <p class="text-sm text-gray-500">Panduan lengkap untuk pengalaman belanja yang mudah dan menyenangkan</p>
        </div>

        <!-- Category filter tabs -->
        <div x-data="{ activeCategory: 'all' }">
            <div class="flex flex-wrap gap-2 justify-center mb-10">
                <template x-for="tab in [
                    { key: 'all', label: 'Semua' },
                    { key: 'pemula', label: 'Untuk Pemula' },
                    { key: 'pembayaran', label: 'Pembayaran' },
                    { key: 'pengiriman', label: 'Pengiriman' },
                ]" :key="tab.key">
                    <button @click="activeCategory = tab.key"
                            :class="activeCategory === tab.key
                                ? 'bg-red-600 text-white border-red-600'
                                : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                            class="px-5 py-2 rounded-full text-sm font-medium border transition-colors"
                            x-text="tab.label">
                    </button>
                </template>
            </div>

            <!-- ===================== -->
            <!-- Panduan Cara Belanja  -->
            <!-- ===================== -->
            <div class="mb-14">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 text-center">Panduan Belanja</h2>

                <!-- 1. Langkah-Langkah Belanja -->
                <div :class="activeCategory !== 'all' && activeCategory !== 'pemula' ? 'hidden' : ''" class="mb-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-sm font-bold flex-shrink-0">1</span>
                            <h3 class="text-base font-semibold text-gray-900">Langkah-Langkah Belanja</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-5">
                                @php
                                    $steps = [
                                        ['label' => 'A', 'title' => 'Jelajahi Produk', 'desc' => 'Gunakan menu kategori atau fitur pencarian untuk menemukan produk yang Anda cari. Anda bisa melihat deskripsi lengkap, harga, dan stok produk.'],
                                        ['label' => 'B', 'title' => 'Tambah ke Keranjang', 'desc' => 'Pilih produk yang Anda inginkan dan klik tombol "Tambah ke Keranjang". Anda dapat menambahkan beberapa produk sekaligus.'],
                                        ['label' => 'C', 'title' => 'Masuk ke Akun Anda', 'desc' => 'Sebelum checkout, Anda harus login atau membuat akun baru. Ini memastikan pesanan Anda aman dan terverifikasi.'],
                                        ['label' => 'D', 'title' => 'Checkout', 'desc' => 'Buka keranjang Anda dan klik "Lanjut ke Checkout". Periksa kembali item yang akan dibeli dan pastikan alamat pengiriman sudah benar.'],
                                        ['label' => 'E', 'title' => 'Pilih Metode Pembayaran', 'desc' => 'Pilih metode pembayaran yang Anda inginkan (Transfer Bank, E-wallet, atau COD). Ikuti petunjuk pembayaran sesuai metode yang dipilih.'],
                                        ['label' => 'F', 'title' => 'Selesai', 'desc' => 'Setelah pembayaran berhasil, Anda akan menerima konfirmasi pesanan. Lacak status pesanan Anda di halaman profil.'],
                                    ];
                                @endphp

                                @foreach($steps as $step)
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-9 h-9 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-sm font-semibold">{{ $step['label'] }}</div>
                                    </div>
                                    <div class="pt-1">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ $step['title'] }}</h4>
                                        <p class="text-sm text-gray-500 mt-0.5">{{ $step['desc'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Membuat Akun -->
                <div :class="activeCategory !== 'all' && activeCategory !== 'pemula' ? 'hidden' : ''" class="mb-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-sm font-bold flex-shrink-0">2</span>
                            <h3 class="text-base font-semibold text-gray-900">Membuat Akun</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @php
                                    $accountSteps = [
                                        'Klik tombol "Daftar" di halaman utama atau login',
                                        'Masukkan nama lengkap, nomor HP, email, dan kata sandi',
                                        'Verifikasi email Anda (cek inbox atau folder spam)',
                                        'Akun Anda siap digunakan!',
                                    ];
                                @endphp

                                @foreach($accountSteps as $i => $text)
                                <div class="flex items-start gap-3">
                                    <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-semibold flex-shrink-0 mt-0.5">{{ $i + 1 }}</span>
                                    <p class="text-sm text-gray-700">{{ $text }}</p>
                                </div>
                                @endforeach
                            </div>

                            <div class="bg-red-50 rounded-xl p-4 mt-5">
                                <p class="text-sm text-red-800">
                                    <span class="font-semibold">Tips:</span> Gunakan email yang aktif dan kata sandi yang mudah diingat namun sulit ditebak orang lain.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Metode Pembayaran -->
                <div :class="activeCategory !== 'all' && activeCategory !== 'pembayaran' ? 'hidden' : ''" class="mb-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-sm font-bold flex-shrink-0">3</span>
                            <h3 class="text-base font-semibold text-gray-900">Metode Pembayaran</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Transfer Bank / E-Wallet -->
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <h4 class="text-sm font-semibold text-gray-900">Transfer Bank / E-Wallet</h4>
                                </div>
                                <ul class="space-y-2 ml-7">
                                    <li class="text-sm text-gray-600 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Transfer Bank BCA, Mandiri, BRI, dan bank lainnya
                                    </li>
                                    <li class="text-sm text-gray-600 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        E-wallet (OVO, Dana, GoPay, dll)
                                    </li>
                                    <li class="text-sm text-gray-600 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Pembayaran diverifikasi otomatis via Midtrans
                                    </li>
                                </ul>
                            </div>

                            <hr class="border-gray-100">

                            <!-- COD -->
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <h4 class="text-sm font-semibold text-gray-900">Cash on Delivery (COD)</h4>
                                </div>
                                <ul class="space-y-2 ml-7">
                                    <li class="text-sm text-gray-600 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Bayar langsung ke kurir saat barang diterima
                                    </li>
                                    <li class="text-sm text-gray-600 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Cocok jika ingin cek barang terlebih dahulu
                                    </li>
                                    <li class="text-sm text-gray-600 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Syarat & ketentuan COD berlaku
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Pengiriman & Logistik -->
                <div :class="activeCategory !== 'all' && activeCategory !== 'pengiriman' ? 'hidden' : ''" class="mb-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition hover:shadow-md">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-sm font-bold flex-shrink-0">4</span>
                            <h3 class="text-base font-semibold text-gray-900">Pengiriman & Logistik</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <h4 class="text-sm font-semibold text-gray-900">Info Pengiriman</h4>
                                    </div>
                                    <ul class="space-y-1.5">
                                        <li class="text-sm text-gray-600 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Ongkir dihitung sesuai lokasi
                                        </li>
                                        <li class="text-sm text-gray-600 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Proses 1-7 hari kerja
                                        </li>
                                        <li class="text-sm text-gray-600 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Paket diasuransikan
                                        </li>
                                    </ul>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <h4 class="text-sm font-semibold text-gray-900">Lacak Pesanan</h4>
                                    </div>
                                    <ul class="space-y-1.5">
                                        <li class="text-sm text-gray-600 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Cek status real-time di profil
                                        </li>
                                        <li class="text-sm text-gray-600 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Notifikasi tiap update
                                        </li>
                                        <li class="text-sm text-gray-600 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Nomor tracking tersedia
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 mt-4">
                                <p class="text-sm text-amber-800">
                                    <span class="font-semibold">Catatan:</span> Pastikan alamat pengiriman sudah benar sebelum checkout. Biaya pengiriman ulang ditanggung pelanggan jika ada kesalahan alamat.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== -->
            <!-- FAQ Section           -->
            <!-- ===================== -->
            <div class="pt-8 border-t border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 text-center">Pertanyaan Umum (FAQ)</h2>

                @php
                    $faqs = [
                        ['q' => 'Apakah saya harus membuat akun untuk berbelanja?', 'a' => 'Ya, Anda harus membuat akun dan login untuk melakukan pembelian. Ini untuk keamanan transaksi dan memudahkan Anda melacak pesanan serta menyimpan riwayat belanja.'],
                        ['q' => 'Berapa lama produk akan sampai?', 'a' => 'Waktu pengiriman biasanya 1-7 hari kerja tergantung lokasi Anda. Anda dapat melacak paket secara real-time melalui halaman profil setelah pesanan dikonfirmasi.'],
                        ['q' => 'Bagaimana jika saya ingin membatalkan pesanan?', 'a' => 'Anda dapat membatalkan pesanan sebelum dikonfirmasi oleh tim kami. Masuk ke halaman profil, buka detail pesanan, dan klik "Batalkan Pesanan". Jika sudah dikonfirmasi, hubungi customer service.'],
                        ['q' => 'Apakah ada jaminan untuk produk yang cacat atau rusak?', 'a' => 'Ya, kami menjamin semua produk dalam kondisi baik. Jika ada produk rusak atau tidak sesuai, hubungi customer service dalam 24 jam setelah menerima paket untuk proses pengembalian.'],
                        ['q' => 'Bagaimana cara menghubungi customer service?', 'a' => 'Hubungi kami melalui email support@gngmart.com, live chat di website, atau WhatsApp.'],
                        ['q' => 'Apakah data pribadi saya aman?', 'a' => 'Ya, kami menggunakan enkripsi untuk melindungi data pribadi dan informasi pembayaran Anda. Data Anda tidak akan dibagikan kepada pihak ketiga tanpa persetujuan.'],
                        ['q' => 'Apakah ada biaya tersembunyi saat checkout?', 'a' => 'Tidak. Semua biaya (harga produk, ongkos kirim) ditampilkan dengan jelas sebelum pembayaran. Anda dapat melihat rincian lengkap di halaman checkout.'],
                        ['q' => 'Apakah saya bisa mengubah pesanan setelah checkout?', 'a' => 'Anda dapat mengubah pesanan jika status masih "Menunggu Pembayaran" atau "Menunggu Konfirmasi". Setelah dikonfirmasi, Anda harus membatalkan dan membuat pesanan baru.'],
                    ];
                @endphp

                <div x-data="{ expanded: null }" class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                    @foreach($faqs as $i => $faq)
                    <div>
                        <button @click="expanded = expanded === {{ $i }} ? null : {{ $i }}"
                                class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors duration-200">
                            <span class="text-sm font-medium text-gray-900 pr-4">{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0 transition-transform duration-200"
                                 :class="expanded === {{ $i }} ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="expanded === {{ $i }}"
                             x-collapse
                             x-cloak>
                            <div class="px-5 pb-4 pt-0">
                                <p class="text-sm text-gray-500 leading-relaxed">{{ $faq['a'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- CTA: Mulai Belanja -->
        <div class="text-center mt-12 pt-8 border-t border-gray-100">
            <p class="text-sm text-gray-500 mb-4">Sudah siap berbelanja?</p>
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center gap-2 bg-red-600 text-white px-6 py-2.5 rounded-full text-sm font-semibold hover:bg-red-700 transition">
                Mulai Belanja
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>

    </div>
</div>
@endsection
