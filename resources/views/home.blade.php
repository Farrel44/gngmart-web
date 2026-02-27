@extends('layouts.app')

@section('content')

<div class="max-w-screen-xl mx-auto px-6">

    <!-- Carousel Banner -->
    <div class="relative rounded-3xl mb-12 shadow-lg overflow-hidden mt-20" id="carousel">
        <!-- Slides -->
        <div class="carousel-container relative h-64">
            <!-- Slide 1 -->
            <div class="carousel-slide absolute inset-0 rounded-3xl p-10 text-white bg-gradient-to-r from-red-500 to-red-300 flex flex-col justify-center transition-opacity duration-500 opacity-100"
                 style="transition: opacity 0.5s ease-in-out;">
                <h1 class="text-3xl font-bold mb-3">
                    Paket Keluarga Hemat Minggu Ini!
                </h1>

                <p class="text-lg mb-6">
                    Belanja lebih banyak, hemat lebih banyak!
                </p>

                <a href="#"
                   class="bg-white text-red-500 font-semibold px-6 py-3 rounded-full hover:bg-gray-200 transition w-fit">
                    Lihat Promo →
                </a>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-slide absolute inset-0 rounded-3xl p-10 text-white bg-gradient-to-r from-orange-500 to-orange-300 flex flex-col justify-center transition-opacity duration-500 opacity-0"
                 style="transition: opacity 0.5s ease-in-out;">
                <h1 class="text-3xl font-bold mb-3">
                    Diskon Spesial Buah Segar!
                </h1>

                <p class="text-lg mb-6">
                    Dapatkan potongan hingga 40% untuk semua buah pilihan!
                </p>

                <a href="#"
                   class="bg-white text-orange-500 font-semibold px-6 py-3 rounded-full hover:bg-gray-200 transition w-fit">
                    Belanja Sekarang →
                </a>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-slide absolute inset-0 rounded-3xl p-10 text-white bg-gradient-to-r from-rose-500 to-pink-400 flex flex-col justify-center transition-opacity duration-500 opacity-0"
                 style="transition: opacity 0.5s ease-in-out;">
                <h1 class="text-3xl font-bold mb-3">
                    Promo Member Eksklusif!
                </h1>

                <p class="text-lg mb-6">
                    Nikmati keuntungan menjadi member setia kami dengan cashback menarik.
                </p>

                <a href="#"
                   class="bg-white text-rose-500 font-semibold px-6 py-3 rounded-full hover:bg-gray-200 transition w-fit">
                    Daftar Sekarang →
                </a>
            </div>
        </div>

        <!-- Dots Indicator -->
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2">
            <button class="carousel-dot w-3 h-3 rounded-full bg-red-600 transition-all cursor-pointer" data-index="0"></button>
            <button class="carousel-dot w-3 h-3 rounded-full bg-gray-300 transition-all cursor-pointer" data-index="1"></button>
            <button class="carousel-dot w-3 h-3 rounded-full bg-gray-300 transition-all cursor-pointer" data-index="2"></button>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        const totalSlides = slides.length;

        function showSlide(index) {
            // Hide all slides
            slides.forEach(slide => {
                slide.classList.remove('opacity-100');
                slide.classList.add('opacity-0');
            });

            // Remove active dot style
            dots.forEach(dot => {
                dot.classList.remove('bg-red-600');
                dot.classList.add('bg-gray-300');
            });

            // Show current slide
            slides[index].classList.remove('opacity-0');
            slides[index].classList.add('opacity-100');

            // Highlight current dot
            dots[index].classList.remove('bg-gray-300');
            dots[index].classList.add('bg-red-600');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
        }

        // Dot click handlers
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });

        // Auto rotate every 5 seconds
        setInterval(nextSlide, 5000);
    </script>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">
    Kategori
</h2>

<div class="flex gap-4 overflow-x-auto pb-4 mb-12">

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            🍔
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Makanan & Minuman
        </span>
    </div>

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            🏠
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Kebutuhan Rumah Tangga
        </span>
    </div>

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            👜
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Aksesoris
        </span>
    </div>

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            📚
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Alat Tulis
        </span>
    </div>

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            💊
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Kesehatan
        </span>
    </div>

    <div class="flex items-center gap-3 bg-red-50 hover:bg-red-100 transition px-5 py-3 rounded-full min-w-max cursor-pointer">
        <div class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
            💄
        </div>
        <span class="text-sm font-semibold text-gray-700">
            Kecantikan
        </span>
    </div>

</div>


    <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">
        Rekomendasi Mingguan
    </h2>
    <a href="#" class="text-blue-600 text-sm font-semibold hover:underline">
        Lihat Semua
    </a>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-20">

    @php
        $products = [
            ['image'=>'apel.png','label'=>'SEGAR','name'=>'Apel Fuji Premium','weight'=>'1 kg','price'=>'45.000'],
            ['image'=>'susu.png','label'=>'SEGAR','name'=>'Susu Segar Ultra','weight'=>'1 Liter','price'=>'18.500'],
            ['image'=>'roti.png','label'=>'PROMO','name'=>'Roti Gandum Sehat','weight'=>'500 g','price'=>'22.000'],
            ['image'=>'ayam.png','label'=>'SEGAR','name'=>'Daging Ayam Fillet','weight'=>'500 g','price'=>'35.000'],
            ['image'=>'jusjeruk.png','label'=>'MINUMAN','name'=>'Jus Jeruk Segar','weight'=>'250 ml','price'=>'25.000'],
            ['image'=>'cookies.png','label'=>'PROMO','name'=>'Cookies Cokelat Chip','weight'=>'300 g','price'=>'28.500'],
            ['image'=>'tomat.png','label'=>'SEGAR','name'=>'Tomat Segar Organik','weight'=>'500 g','price'=>'15.000'],
            ['image'=>'yogurt.png','label'=>'SEGAR','name'=>'Yogurt Greek Plain','weight'=>'450 g','price'=>'32.000'],
            ['image'=>'mie.png','label'=>'PROMO','name'=>'Mi Instan Goreng','weight'=>'5 pcs','price'=>'12.500'],
            ['image'=>'salmon.png','label'=>'SEGAR','name'=>'Salmon Fillet Segar','weight'=>'300 g','price'=>'85.000'],
            ['image'=>'tehhijau.png','label'=>'MINUMAN','name'=>'Teh Hijau Botol','weight'=>'500 ml','price'=>'8.500'],
            ['image'=>'chips.png','label'=>'PROMO','name'=>'Keripik Kentang BBQ','weight'=>'150 g','price'=>'16.000'],
        ];
    @endphp

    @foreach($products as $product)
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-4">

        <img src="{{ Str::startsWith($product['image'], ['http://','https://']) ? $product['image'] : asset('images/products/' . $product['image']) }}" 
             alt="{{ $product['name'] }}"
             class="h-32 w-full object-cover rounded-xl mb-4 bg-gray-200">

        <span class="text-xs font-bold 
            {{ $product['label'] == 'PROMO' ? 'text-orange-500' : 
               ($product['label'] == 'MINUMAN' ? 'text-blue-500' : 'text-green-500') }}">
            {{ $product['label'] }}
        </span>

        <h3 class="font-semibold text-gray-800 text-sm mt-1">
            {{ $product['name'] }}
        </h3>

        <p class="text-xs text-gray-500 mb-2">
            {{ $product['weight'] }}
        </p>

        <p class="text-green-600 font-bold mb-3">
            Rp {{ $product['price'] }}
        </p>

        <button class="w-full bg-red-600 text-white py-2 rounded-full text-sm hover:bg-red-700 transition">
            Beli
        </button>

    </div>
    @endforeach

</div>

    <!-- Promo Section -->
    <div class="max-w-screen-xl mx-auto px-6 mt-16">
        <div class="relative bg-gradient-to-r from-green-300 via-green-400 to-green-500 rounded-3xl p-12 flex flex-col md:flex-row items-center">
            <div class="flex-1 pr-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Hemat 30% untuk Produk Organik!</h2>
                <p class="text-gray-600 mb-6">Nikmati kesegaran alami dengan harga spesial minggu ini</p>
                <a href="#" class="inline-block bg-red-600 text-white px-6 py-3 rounded-full hover:bg-red-700 transition">
                    Belanja Sekarang
                </a>
            </div>
            <!-- image overlaps outside green box -->
            <img src="{{ asset('images/sayur.png') }}" alt="Promo Organik" class="absolute right-0 top-0 h-full w-auto max-w-md rounded-2xl object-cover">
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100 mt-20 py-12">
        <div class="max-w-screen-xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <img src="{{ asset('images/logo.png') }}" alt="GnG Mart" class="h-10 mb-4">
                <p class="text-gray-600">Belanja kebutuhan keluarga dengan mudah, cepat, dan hemat!</p>
            </div>
            <div>
                <h3 class="font-semibold mb-3">Kategori</h3>
                <ul class="text-gray-600 space-y-1">
                    <li>Makanan dan Minuman</li>
                    <li>Kebutuhan Rumah Tangga</li>
                    <li>Aksesoris</li>
                    <li>Alat Tulis</li>
                    <li>Kesehatan</li>
                    <li>Kecantikan</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-3">Bantuan</h3>
                <ul class="text-gray-600 space-y-1">
                    <li>Cara Belanja</li>
                    <li>Tentang GnG Mart</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-3">Hubungi Kami</h3>
                <p class="text-gray-600 flex items-center gap-2"><span class="text-blue-500">📞</span> 0800-123-4567</p>
            </div>
        </div>
        <div class="text-center text-gray-500 mt-8">
            © 2026 GnG Mart. Semua hak dilindungi.
        </div>
    </footer>

