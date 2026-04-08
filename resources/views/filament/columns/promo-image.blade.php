@if($getState())
    <img src="{{ asset('storage/' . $getState()) }}" alt="Promo Banner Image" class="h-16 w-auto rounded object-cover" />
@else
    <span class="text-gray-400">No image</span>
@endif
