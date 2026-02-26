{{-- 
    Reusable Product Card Component
    
    Menampilkan card produk dengan gambar, nama, kategori, harga, dan status stok.
    Digunakan di landing page, katalog, dan related products.
    
    Props:
    - $product: Model App\Models\Product (harus eager load 'images' dan 'category')
--}}

@props(['product'])

<a href="{{ route('products.show', $product) }}" 
   class="group block bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
    
    {{-- Product Image --}}
    <div class="aspect-square bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
        @if($product->images->isNotEmpty())
            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                 alt="{{ $product->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 loading="lazy">
        @else
            {{-- Placeholder jika tidak ada gambar --}}
            <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif

        {{-- Stock badge - tampil di pojok jika stok menipis --}}
        @if($product->stock <= 5 && $product->stock > 0)
            <span class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-medium px-2 py-1 rounded">
                Stok Terbatas
            </span>
        @endif

        {{-- Discount badge - tampil di pojok kiri atas jika ada diskon --}}
        @if($product->hasDiscount())
            <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                -{{ $product->getDiscountPercentage() }}%
            </span>
        @endif
    </div>

    {{-- Product Info --}}
    <div class="p-4">
        {{-- Category --}}
        @if($product->category)
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                {{ $product->category->name }}
            </p>
        @endif

        {{-- Name --}}
        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
            {{ $product->name }}
        </h3>

        {{-- Price dengan dukungan diskon --}}
        <div class="mt-2">
            @if($product->hasDiscount())
                {{-- Ada diskon: tampilkan harga coret + harga diskon --}}
                <div class="flex items-center gap-2">
                    <span class="text-lg font-semibold text-red-600 dark:text-red-400">
                        Rp {{ number_format($product->discount_price, 0, ',', '.') }}
                    </span>
                    <span class="text-sm text-gray-500 line-through">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </span>
                </div>
                {{-- Badge diskon --}}
                <span class="inline-block mt-1 text-xs font-medium text-red-600 dark:text-red-400">
                    -{{ $product->getDiscountPercentage() }}%
                </span>
            @else
                {{-- Tanpa diskon: harga normal --}}
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </p>
            @endif
        </div>
    </div>
</a>
