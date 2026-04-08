@php
    $firstImage = $getRecord()->images->first();
@endphp

@if($firstImage)
    <img src="{{ asset('storage/' . $firstImage->image_url) }}" alt="Product Image" class="h-10 w-10 rounded-full object-cover" />
@else
    <img src="{{ asset('/images/placeholder.png') }}" alt="Placeholder" class="h-10 w-10 rounded-full object-cover" />
@endif
