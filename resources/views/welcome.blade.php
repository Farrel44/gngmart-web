@extends('layouts.app')

@section('content')

<div class="max-w-screen-xl mx-auto px-6">

    <div class="bg-red-500 rounded-3xl p-10 text-white mb-12 shadow-lg">
        <h1 class="text-3xl font-bold mb-3">
            Paket Keluarga Hemat Minggu Ini!
        </h1>

        <p class="text-lg mb-6">
            Belanja lebih banyak, hemat lebih banyak!
        </p>

        <a href="#"
           class="bg-white text-red-500 font-semibold px-6 py-3 rounded-full hover:bg-gray-200 transition">
            Lihat Promo →
        </a>
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        Kategori Favorit
    </h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-14">

        <div class="bg-white rounded-2xl shadow hover:shadow-lg p-6 text-center">
            <div class="text-4xl mb-3">🥤</div>
            <p class="font-semibold text-gray-800">Minuman</p>
        </div>

        <div class="bg-white rounded-2xl shadow hover:shadow-lg p-6 text-center">
            <div class="text-4xl mb-3">🍜</div>
            <p class="font-semibold text-gray-800">Makanan Instan</p>
        </div>

        <div class="bg-white rounded-2xl shadow hover:shadow-lg p-6 text-center">
            <div class="text-4xl mb-3">🧴</div>
            <p class="font-semibold text-gray-800">Perawatan</p>
        </div>

        <div class="bg-white rounded-2xl shadow hover:shadow-lg p-6 text-center">
            <div class="text-4xl mb-3">🧼</div>
            <p class="font-semibold text-gray-800">Kebutuhan Rumah</p>
        </div>

    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        Produk Terbaru
    </h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-20">

        <div class="bg-white rounded-2xl shadow hover:shadow-lg p-4">
            <div class="h-40 bg-gray-200 rounded-xl mb-4"></div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Indomie Goreng</h3>
            <p class="text-red-500 font-bold mb-3 text-sm">Rp 3.500</p>
            <button class="w-full bg-red-500 text-white py-2 rounded-xl text-sm hover:bg-red-600 transition">
                + Keranjang
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow hover:shadow-lg p-4">
            <div class="h-40 bg-gray-200 rounded-xl mb-4"></div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Aqua 600ml</h3>
            <p class="text-red-500 font-bold mb-3 text-sm">Rp 4.000</p>
            <button class="w-full bg-red-500 text-white py-2 rounded-xl text-sm hover:bg-red-600 transition">
                + Keranjang
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow hover:shadow-lg p-4">
            <div class="h-40 bg-gray-200 rounded-xl mb-4"></div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Sabun Lifebuoy</h3>
            <p class="text-red-500 font-bold mb-3 text-sm">Rp 6.000</p>
            <button class="w-full bg-red-500 text-white py-2 rounded-xl text-sm hover:bg-red-600 transition">
                + Keranjang
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow hover:shadow-lg p-4">
            <div class="h-40 bg-gray-200 rounded-xl mb-4"></div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Beras 5kg</h3>
            <p class="text-red-500 font-bold mb-3 text-sm">Rp 65.000</p>
            <button class="w-full bg-red-500 text-white py-2 rounded-xl text-sm hover:bg-red-600 transition">
                + Keranjang
            </button>
        </div>

    </div>

</div>

@endsection
