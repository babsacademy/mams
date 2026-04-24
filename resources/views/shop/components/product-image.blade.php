@props([
    'src' => null,
    'alt' => 'Product Image',
    'class' => 'w-full h-full object-cover',
    'loading' => 'lazy',
])

@php
    use App\Helpers\ImageHelper;
    $imageUrl = ImageHelper::getImageUrl($src);
@endphp

<img 
    src="{{ $imageUrl }}"
    alt="{{ $alt }}"
    class="{{ $class }}"
    loading="{{ $loading }}"
    onerror="this.onerror=null; this.src='{{ asset('assets/images/logo.svg') }}';"
    {{ $attributes }}
>
