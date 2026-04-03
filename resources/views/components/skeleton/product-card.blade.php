{{--
    Product Card Skeleton

    Skeleton placeholder yang meniru layout <x-product-card>.
    Digunakan saat loading produk atau sebagai placeholder selama Alpine hydration.

    Props:
    - count: jumlah skeleton card yang ditampilkan (default: 1)
--}}

@props(['count' => 1])

@for($i = 0; $i < $count; $i++)
<div class="bg-white rounded-2xl shadow-sm p-4 flex flex-col h-full animate-pulse">
    {{-- Image placeholder --}}
    <div class="h-32 w-full rounded-xl mb-4 skeleton-shimmer"></div>

    {{-- Category --}}
    <div class="h-3 w-16 rounded skeleton-shimmer mb-2"></div>

    {{-- Name (2 lines) --}}
    <div class="h-4 w-full rounded skeleton-shimmer mb-1"></div>
    <div class="h-4 w-3/4 rounded skeleton-shimmer mb-2"></div>

    {{-- Weight --}}
    <div class="h-3 w-12 rounded skeleton-shimmer mb-3"></div>

    {{-- Price --}}
    <div class="mb-3">
        <div class="h-3 w-16 rounded skeleton-shimmer mb-1"></div>
        <div class="h-5 w-24 rounded skeleton-shimmer"></div>
    </div>

    {{-- Button --}}
    <div class="h-10 w-full rounded-full skeleton-shimmer mt-auto"></div>
</div>
@endfor
