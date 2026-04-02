@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-b from-red-50 to-white min-h-screen pt-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Cara Berbelanja di GnG Mart</h1>
            <p class="text-lg text-gray-600">Panduan lengkap untuk pengalaman belanja yang mudah dan menyenangkan</p>
        </div>

        <!-- Search and FAQs Navigation -->
        <div class="mb-8" x-data="{ activeCategory: 'all' }">
            <div class="flex flex-wrap gap-3 justify-center mb-8">
                <button @click="activeCategory = 'all'" 
                        :class="activeCategory === 'all' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 border-2 border-gray-300'"
                        class="px-6 py-2 rounded-full font-semibold transition">
                    Semua
                </button>
                <button @click="activeCategory = 'pemula'" 
                        :class="activeCategory === 'pemula' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 border-2 border-gray-300'"
                        class="px-6 py-2 rounded-full font-semibold transition">
                    Untuk Pemula
                </button>
                <button @click="activeCategory = 'pembayaran'" 
                        :class="activeCategory === 'pembayaran' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 border-2 border-gray-300'"
                        class="px-6 py-2 rounded-full font-semibold transition">
                    Pembayaran
                </button>
                <button @click="activeCategory = 'pengiriman'" 
                        :class="activeCategory === 'pengiriman' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 border-2 border-gray-300'"
                        class="px-6 py-2 rounded-full font-semibold transition">
                    Pengiriman
                </button>
            </div>

            <!-- Cara Belanja Section Title -->
            <div class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Cara Belanja</h2>
                
                <!-- Panduan Utama -->
                <div class="space-y-6">
                <!-- Langkah-langkah Berbelanja -->
                <div :class="activeCategory !== 'all' && activeCategory !== 'pemula' ? 'hidden' : ''" class="transition">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                            <h2 class="text-2xl font-bold text-white flex items-center">
                                <span class="bg-white text-red-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 font-bold">1</span>
                                Langkah-Langkah Belanja
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600 font-bold">A</div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Jelajahi Produk</h3>
                                        <p class="text-gray-600 mt-1">Gunakan menu kategori atau fitur pencarian untuk menemukan produk yang Anda cari. Anda bisa melihat deskripsi lengkap, harga, dan stok produk.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600 font-bold">B</div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Tambah ke Keranjang</h3>
                                        <p class="text-gray-600 mt-1">Pilih produk yang Anda inginkan dan klik tombol "Tambah ke Keranjang". Anda dapat menambahkan beberapa produk sekaligus. Keranjang Anda akan tersimpan bahkan jika Anda keluar dari aplikasi.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600 font-bold">C</div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Masuk ke Akun Anda</h3>
                                        <p class="text-gray-600 mt-1">Sebelum checkout, Anda harus login atau membuat akun baru. Ini memastikan pesanan Anda aman dan terverifikasi dengan baik.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600 font-bold">D</div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Checkout</h3>
                                        <p class="text-gray-600 mt-1">Buka keranjang Anda dan klik "Lanjut ke Checkout". Periksa kembali item yang akan dibeli dan pastikan alamat pengiriman sudah benar.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600 font-bold">E</div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Pilih Metode Pembayaran</h3>
                                        <p class="text-gray-600 mt-1">Pilih metode pembayaran yang Anda inginkan (Transfer Bank atau COD). Ikuti petunjuk pembayaran sesuai metode yang dipilih.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600 font-bold">F</div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Selesai</h3>
                                        <p class="text-gray-600 mt-1">Setelah pembayaran berhasil, Anda akan menerima konfirmasi pesanan melalui email. Lacak status pesanan Anda di halaman profil.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Membuat Akun -->
                <div :class="activeCategory !== 'all' && activeCategory !== 'pemula' ? 'hidden' : ''" class="transition">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                            <h2 class="text-2xl font-bold text-white flex items-center">
                                <span class="bg-white text-blue-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 font-bold">2</span>
                                Membuat Akun
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3 text-gray-700">
                                <p><strong>Langkah 1:</strong> Klik tombol "Daftar" di halaman utama atau login</p>
                                <p><strong>Langkah 2:</strong> Masukkan nama lengkap, email, dan password yang kuat</p>
                                <p><strong>Langkah 3:</strong> Verifikasi email Anda (cek inbox atau folder spam)</p>
                                <p><strong>Langkah 4:</strong> Akun Anda siap digunakan!</p>
                                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mt-4">
                                    <p class="text-blue-900"><strong>💡 Tips:</strong> Pastikan Anda menggunakan email yang aktif dan password yang mudah diingat namun sulit ditebak orang lain.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div :class="activeCategory !== 'all' && activeCategory !== 'pembayaran' ? 'hidden' : ''" class="transition">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                            <h2 class="text-2xl font-bold text-white flex items-center">
                                <span class="bg-white text-green-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 font-bold">3</span>
                                Metode Pembayaran
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Transfer Bank -->
                            <div class="border-b pb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                    <span class="text-2xl mr-2">🏦</span>
                                    Transfer Bank / E-Wallet
                                </h3>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-gray-700 mb-3">Metode pembayaran yang aman dan terpercaya melalui Midtrans:</p>
                                    <ul class="list-disc list-inside text-gray-700 space-y-2">
                                        <li>Transfer Bank BCA, Mandiri, BRI, dan bank lainnya</li>
                                        <li>Pembayaran melalui e-wallet (GCash, OVO, Dana, dll)</li>
                                        <li>Pembayaran akan langsung terverifikasi</li>
                                        <li>Pesanan akan diproses setelah pembayaran dikonfirmasi</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- COD -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                    <span class="text-2xl mr-2">🚚</span>
                                    Cash on Delivery (COD)
                                </h3>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-gray-700 mb-3">Bayar saat barang sampai ke tangan Anda:</p>
                                    <ul class="list-disc list-inside text-gray-700 space-y-2">
                                        <li>Anda dapat membayar langsung kepada kurir saat barang diterima</li>
                                        <li>Cocok jika Anda ingin melihat barang terlebih dahulu sebelum membayar</li>
                                        <li>Syarat & ketentuan COD berlaku sesuai kebijakan pengiriman</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengiriman dan Logistik -->
                <div :class="activeCategory !== 'all' && activeCategory !== 'pengiriman' ? 'hidden' : ''" class="transition">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 py-4">
                            <h2 class="text-2xl font-bold text-white flex items-center">
                                <span class="bg-white text-orange-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 font-bold">4</span>
                                Pengiriman & Logistik
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-orange-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-gray-900 mb-2">📋 Informasi Pengiriman</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>✓ Ongkos kirim dihitung sesuai lokasi</li>
                                        <li>✓ Proses pengiriman 1-7 hari kerja</li>
                                        <li>✓ Paket diasuransikan</li>
                                        <li>✓ Gratis ongkos kirim untuk pembelian tertentu</li>
                                    </ul>
                                </div>
                                <div class="bg-orange-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-gray-900 mb-2">🔍 Lacak Pesanan</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>✓ Cek status real-time di profil Anda</li>
                                        <li>✓ Notifikasi email untuk setiap update</li>
                                        <li>✓ Nomor tracking untuk pelacakan kurir</li>
                                        <li>✓ Hubungi CS jika ada masalah</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mt-4">
                                <p class="text-yellow-900"><strong>⚠️ Catatan Penting:</strong> Pastikan alamat pengiriman sudah benar sebelum checkout. Biaya pengiriman ulang ditanggung pelanggan jika ada kesalahan alamat.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mt-12 pt-8 border-t-2 border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">FAQ</h2>
                    
                    <div x-data="{ expanded: null }" class="space-y-3">
                        <!-- FAQ Item 1 -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <button @click="expanded = expanded === 1 ? null : 1"
                                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <h3 class="text-lg font-semibold text-gray-900 text-left">Apakah saya harus membuat akun untuk berbelanja?</h3>
                                <span :class="expanded === 1 ? 'rotate-180' : ''" class="text-2xl transition">
                                    ▼
                                </span>
                            </button>
                            <div x-show="expanded === 1" class="border-t px-6 py-4 bg-gray-50">
                                <p class="text-gray-700">Ya, Anda harus membuat akun dan login untuk melakukan pembelian. Ini untuk keamanan transaksi dan memudahkan Anda melacak pesanan serta menyimpan riwayat belanja.</p>
                            </div>
                        </div>

                        <!-- FAQ Item 2 -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <button @click="expanded = expanded === 2 ? null : 2"
                                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <h3 class="text-lg font-semibold text-gray-900 text-left">Berapa lama produk akan sampai?</h3>
                                <span :class="expanded === 2 ? 'rotate-180' : ''" class="text-2xl transition">
                                    ▼
                                </span>
                            </button>
                            <div x-show="expanded === 2" class="border-t px-6 py-4 bg-gray-50">
                                <p class="text-gray-700">Waktu pengiriman biasanya 1-7 hari kerja tergantung lokasi Anda. Anda dapat melacak paket Anda secara real-time melalui halaman profil Anda setelah pesanan dikonfirmasi.</p>
                            </div>
                        </div>

                        <!-- FAQ Item 3 -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <button @click="expanded = expanded === 3 ? null : 3"
                                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <h3 class="text-lg font-semibold text-gray-900 text-left">Bagaimana jika saya ingin membatalkan pesanan?</h3>
                                <span :class="expanded === 3 ? 'rotate-180' : ''" class="text-2xl transition">
                                    ▼
                                </span>
                            </button>
                            <div x-show="expanded === 3" class="border-t px-6 py-4 bg-gray-50">
                                <p class="text-gray-700">Anda dapat membatalkan pesanan sebelum dikonfirmasi oleh tim kami. Masuk ke halaman profil Anda, buka detail pesanan, dan klik tombol "Batalkan Pesanan". Jika sudah dikonfirmasi, hubungi customer service kami.</p>
                            </div>
                        </div>

                        <!-- FAQ Item 4 -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <button @click="expanded = expanded === 4 ? null : 4"
                                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <h3 class="text-lg font-semibold text-gray-900 text-left">Apakah ada jaminan untuk produk yang cacat atau rusak?</h3>
                                <span :class="expanded === 4 ? 'rotate-180' : ''" class="text-2xl transition">
                                    ▼
                                </span>
                            </button>
                            <div x-show="expanded === 4" class="border-t px-6 py-4 bg-gray-50">
                                <p class="text-gray-700">Ya, kami menjamin semua produk dalam kondisi baik. Jika ada produk yang rusak atau tidak sesuai, Anda dapat menghubungi customer service kami dalam 24 jam setelah menerima paket untuk proses pengembalian atau penggantian.</p>
                            </div>
                        </div>

                        <!-- FAQ Item 5 -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <button @click="expanded = expanded === 5 ? null : 5"
                                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <h3 class="text-lg font-semibold text-gray-900 text-left">Bagaimana cara menghubungi customer service?</h3>
                                <span :class="expanded === 5 ? 'rotate-180' : ''" class="text-2xl transition">
                                    ▼
                                </span>
                            </button>
                            <div x-show="expanded === 5" class="border-t px-6 py-4 bg-gray-50">
                                <p class="text-gray-700">Anda dapat menghubungi customer service kami melalui:</p>
                                <ul class="list-disc list-inside text-gray-700 mt-2 space-y-1">
                                    <li>📧 Email: support@gngmart.com</li>
                                    <li>💬 Live Chat di website</li>
                                    <li>📞 WhatsApp: +62 xxx xxxx xxxx</li>
                                </ul>
                            </div>
                        </div>

                        <!-- FAQ Item 6 -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <button @click="expanded = expanded === 6 ? null : 6"
                                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <h3 class="text-lg font-semibold text-gray-900 text-left">Apakah data pribadi saya aman?</h3>
                                <span :class="expanded === 6 ? 'rotate-180' : ''" class="text-2xl transition">
                                    ▼
                                </span>
                            </button>
                            <div x-show="expanded === 6" class="border-t px-6 py-4 bg-gray-50">
                                <p class="text-gray-700">Ya, kami menggunakan enkripsi tingkat bank untuk melindungi data pribadi dan informasi pembayaran Anda. Data Anda tidak akan pernah dibagikan kepada pihak ketiga tanpa persetujuan Anda.</p>
                            </div>
                        </div>

                        <!-- FAQ Item 7 -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <button @click="expanded = expanded === 7 ? null : 7"
                                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <h3 class="text-lg font-semibold text-gray-900 text-left">Apakah ada biaya tersembunyi saat checkout?</h3>
                                <span :class="expanded === 7 ? 'rotate-180' : ''" class="text-2xl transition">
                                    ▼
                                </span>
                            </button>
                            <div x-show="expanded === 7" class="border-t px-6 py-4 bg-gray-50">
                                <p class="text-gray-700">Tidak ada biaya tersembunyi. Semua biaya (harga produk, ongkos kirim, pajak) ditampilkan dengan jelas sebelum Anda melakukan pembayaran. Anda dapat melihat rincian lengkap di halaman checkout.</p>
                            </div>
                        </div>

                        <!-- FAQ Item 8 -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <button @click="expanded = expanded === 8 ? null : 8"
                                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <h3 class="text-lg font-semibold text-gray-900 text-left">Apakah saya bisa mengubah pesanan setelah checkout?</h3>
                                <span :class="expanded === 8 ? 'rotate-180' : ''" class="text-2xl transition">
                                    ▼
                                </span>
                            </button>
                            <div x-show="expanded === 8" class="border-t px-6 py-4 bg-gray-50">
                                <p class="text-gray-700">Anda dapat mengubah pesanan jika status masih "Menunggu Pembayaran" atau "Menunggu Konfirmasi". Setelah pesanan dikonfirmasi oleh tim kami, Anda harus membatalkan dan membuat pesanan baru. Hubungi customer service untuk bantuan lebih lanjut.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
