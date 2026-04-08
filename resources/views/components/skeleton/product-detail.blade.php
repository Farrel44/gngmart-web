{{--
    Product Detail Skeleton

    Skeleton placeholder yang meniru layout halaman detail produk.
    Menampilkan placeholder untuk: image gallery, product info, dan purchase card.
--}}

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 animate-pulse">

    {{-- LEFT: Image gallery --}}
    <div class="lg:col-span-4">
        <div class="aspect-square rounded-xl skeleton-shimmer"></div>
        <div class="flex gap-3 mt-4 justify-center">
            @for($i = 0; $i < 4; $i++)
                <div class="w-16 h-16 rounded-lg skeleton-shimmer"></div>
            @endfor
        </div>
    </div>

    {{-- CENTER: Product info --}}
    <div class="lg:col-span-5 space-y-5">
        {{-- Name --}}
        <div>
            <div class="h-7 w-3/4 rounded skeleton-shimmer mb-2"></div>
            <div class="h-7 w-1/2 rounded skeleton-shimmer"></div>
        </div>

        {{-- Price --}}
        <div class="h-8 w-40 rounded skeleton-shimmer"></div>

        {{-- Info card --}}
        <div class="rounded-xl border border-gray-200 p-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg skeleton-shimmer"></div>
                <div>
                    <div class="h-4 w-32 rounded skeleton-shimmer mb-1"></div>
                    <div class="h-3 w-24 rounded skeleton-shimmer"></div>
                </div>
            </div>
            <div class="border-t border-gray-100 pt-3 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg skeleton-shimmer"></div>
                <div>
                    <div class="h-4 w-28 rounded skeleton-shimmer mb-1"></div>
                    <div class="h-3 w-20 rounded skeleton-shimmer"></div>
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="rounded-xl border border-gray-200 p-5">
            <div class="h-4 w-32 rounded skeleton-shimmer mb-3"></div>
            <div class="space-y-2">
                <div class="h-3 w-full rounded skeleton-shimmer"></div>
                <div class="h-3 w-full rounded skeleton-shimmer"></div>
                <div class="h-3 w-2/3 rounded skeleton-shimmer"></div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Purchase card --}}
    <div class="lg:col-span-3">
        <div class="rounded-2xl border border-gray-200 p-5 space-y-4">
            <div class="h-6 w-32 rounded skeleton-shimmer"></div>
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full skeleton-shimmer"></div>
                <div class="h-3 w-20 rounded skeleton-shimmer"></div>
            </div>
            <div class="space-y-2.5 pt-2 border-t border-gray-100">
                <div class="h-11 w-full rounded-xl skeleton-shimmer"></div>
                <div class="h-11 w-full rounded-xl skeleton-shimmer"></div>
            </div>
        </div>
    </div>
</div>
