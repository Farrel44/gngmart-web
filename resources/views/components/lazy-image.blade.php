{{--
    Lazy Image with Skeleton Shimmer

    Menampilkan shimmer skeleton saat gambar sedang dimuat,
    lalu fade-in saat gambar berhasil load. Jika gagal, fallback ke placeholder.

    Props:
    - src: URL gambar
    - alt: alt text
    - imgClass: class tambahan untuk tag <img> (default: w-full h-full object-cover)
    - fallback: URL gambar fallback jika error (default: placeholder.png)

    Usage:
    <x-lazy-image :src="$imageUrl" :alt="$product->name" class="h-32 rounded-xl" />
--}}

@props([
    'src',
    'alt' => '',
    'imgClass' => 'w-full h-full object-cover',
    'fallback' => null,
])

@php
    $fallbackSrc = $fallback ?? asset('images/placeholder.png');
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-gray-100']) }}>
    <div class="absolute inset-0 skeleton-shimmer" aria-hidden="true"></div>
    <img src="{{ $src }}"
         alt="{{ $alt }}"
         loading="lazy"
         onload="this.style.opacity='1';this.previousElementSibling.remove()"
         onerror="this.style.opacity='1';this.previousElementSibling.remove();this.src='{{ $fallbackSrc }}'"
         style="opacity: 0"
         class="relative z-10 transition-opacity duration-300 {{ $imgClass }}">
</div>
