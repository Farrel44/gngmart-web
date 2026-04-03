{{--
    Order Card Skeleton

    Skeleton placeholder yang meniru layout order card di riwayat pesanan.

    Props:
    - count: jumlah skeleton cards (default: 3)
--}}

@props(['count' => 3])

@for($i = 0; $i < $count; $i++)
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden animate-pulse">
    {{-- Header --}}
    <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="h-4 w-16 rounded skeleton-shimmer"></div>
            <div class="h-3 w-24 rounded skeleton-shimmer"></div>
        </div>
        <div class="h-6 w-28 rounded-full skeleton-shimmer"></div>
    </div>

    {{-- Body --}}
    <div class="px-5 py-4 space-y-3">
        @for($j = 0; $j < 2; $j++)
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg skeleton-shimmer flex-shrink-0"></div>
            <div class="flex-1">
                <div class="h-4 w-3/4 rounded skeleton-shimmer mb-1"></div>
                <div class="h-3 w-1/3 rounded skeleton-shimmer"></div>
            </div>
        </div>
        @endfor
    </div>

    {{-- Footer --}}
    <div class="px-5 py-3 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
        <div>
            <div class="h-3 w-28 rounded skeleton-shimmer mb-1"></div>
            <div class="h-5 w-32 rounded skeleton-shimmer"></div>
        </div>
        <div class="h-9 w-20 rounded-lg skeleton-shimmer"></div>
    </div>
</div>
@endfor
