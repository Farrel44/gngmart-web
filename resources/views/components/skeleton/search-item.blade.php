{{--
    Search Suggestion Skeleton

    Skeleton placeholder untuk suggestion items di dropdown pencarian.
    Meniru layout baris teks suggestion.

    Props:
    - count: jumlah skeleton rows (default: 5)
--}}

@props(['count' => 5])

<div class="py-2 px-1">
    @for($i = 0; $i < $count; $i++)
    <div class="flex items-center gap-3 px-3 py-2.5">
        <div class="w-4 h-4 rounded skeleton-shimmer flex-shrink-0"></div>
        <div class="h-4 rounded skeleton-shimmer" style="width: {{ rand(40, 80) }}%"></div>
    </div>
    @endfor
</div>
