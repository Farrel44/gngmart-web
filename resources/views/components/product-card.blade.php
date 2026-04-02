{{-- 
    Reusable Product Card Component
    
    Menampilkan card produk dengan gambar, nama, kategori, harga, dan tombol beli.
    Digunakan di landing page, katalog, dan related products.
    
    Props:
    - $product: Model App\Models\Product (harus eager load 'images' dan 'category')
--}}

@props(['product'])

@php
    $imageUrl = $product->images->isNotEmpty()
        ? asset('storage/' . $product->images->first()->image_url)
        : asset('images/placeholder.png');

    $effectivePrice = $product->getEffectivePrice();
    $discountPct = $product->getDiscountPercentage();
    $hasAnyDiscount = $discountPct > 0;
    $promo = $product->getBestActivePromotion();
@endphp

<a href="{{ route('products.show', $product) }}"
   class="group bg-white rounded-2xl shadow-sm hover:shadow-md transition p-4 flex flex-col h-full">

    {{-- Product Image --}}
    <div class="relative">
        <img src="{{ $imageUrl }}"
             alt="{{ $product->name }}"
             class="h-32 w-full object-cover rounded-xl mb-4 bg-gray-200"
             loading="lazy">

        {{-- Discount badge --}}
        @if($hasAnyDiscount)
            <span class="absolute top-2 left-2 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                -{{ $discountPct }}%
            </span>
        @endif

        {{-- Stock badge --}}
        @if($product->stock <= 5 && $product->stock > 0)
            <span class="absolute top-2 right-2 bg-yellow-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                Stok Terbatas
            </span>
        @endif
    </div>

    {{-- Category --}}
    @if($product->category)
        <span class="text-xs font-bold text-green-500 uppercase">
            {{ $product->category->name }}
        </span>
    @endif

    {{-- Name --}}
    <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 mt-1 group-hover:text-red-600 transition-colors">
        {{ $product->name }}
    </h3>

    @if($product->weight)
        <p class="text-xs text-gray-500 mb-2">{{ $product->weight }}</p>
    @endif

    {{-- Price --}}
    <div class="mb-3">
        @if($hasAnyDiscount)
            <p class="text-xs text-gray-400 line-through">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </p>
            <p class="text-red-600 font-bold">
                Rp {{ number_format($effectivePrice, 0, ',', '.') }}
            </p>
        @else
            <p class="text-red-600 font-bold">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </p>
        @endif

        @if($promo)
            <span class="text-[10px] bg-red-50 text-red-600 font-medium px-1.5 py-0.5 rounded mt-1 inline-block">
                {{ $promo->name }}
            </span>
        @endif
    </div>

    {{-- Buy Button --}}
    <span class="w-full bg-red-600 text-white py-2 rounded-full text-sm hover:bg-red-700 transition mt-auto block text-center">
        Beli
    </span>
</a>
