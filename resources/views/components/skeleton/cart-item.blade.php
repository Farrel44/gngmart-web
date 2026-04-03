{{--
    Cart Item Skeleton

    Skeleton placeholder yang meniru layout item di halaman keranjang.
    Digunakan saat Alpine hydration (sebelum cartPage() siap).

    Props:
    - count: jumlah skeleton items (default: 3)
--}}

@props(['count' => 3])

@for($i = 0; $i < $count; $i++)
<div class="bg-white rounded-2xl shadow-sm p-4 flex gap-4 items-start animate-pulse">
    {{-- Checkbox --}}
    <div class="mt-2 h-4 w-4 rounded border border-gray-200 skeleton-shimmer flex-shrink-0"></div>

    {{-- Thumbnail --}}
    <div class="w-20 h-20 rounded-xl skeleton-shimmer flex-shrink-0"></div>

    {{-- Info --}}
    <div class="flex-1 min-w-0">
        {{-- Name --}}
        <div class="h-4 w-3/4 rounded skeleton-shimmer mb-2"></div>

        {{-- Price --}}
        <div class="h-4 w-24 rounded skeleton-shimmer mb-3"></div>

        {{-- Quantity controls --}}
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg skeleton-shimmer"></div>
            <div class="w-10 h-5 rounded skeleton-shimmer"></div>
            <div class="w-8 h-8 rounded-lg skeleton-shimmer"></div>
        </div>
    </div>
</div>
@endfor
