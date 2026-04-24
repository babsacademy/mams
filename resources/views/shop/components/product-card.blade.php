@props([
    'product',
    'showAddToCart' => true,
    'compact'       => false,
])

@php
    $image       = $product->image_url ?? asset('assets/images/placeholder-product.svg');
    $price       = $product->price;
    $originalPrice = $product->price;
    $hasPromo    = !empty($product->original_price);
    $isNew       = !empty($product->is_new);
    $inStock     = ($product->stock ?? 0) > 0;
    $categoryName = $product->category?->name ?? '';
    $discount    = $hasPromo && $originalPrice > 0
        ? round((1 - $price / $originalPrice) * 100)
        : 0;
@endphp

<div class="group flex flex-col">

  {{-- ── Image Container ── --}}
  <a href="{{ route('products.show', $product) }}"
     class="block relative overflow-hidden bg-gray-100 {{ $compact ? 'aspect-square' : 'aspect-[3/4]' }}">

    <img
        src="{{ $image }}"
        alt="{{ $product->name }}"
        class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-500"
        loading="lazy"
    >

    {{-- Badges — top left --}}
    <div class="absolute top-3 left-3 flex flex-col gap-1.5">
      @if ($isNew)
        <span class="bg-accent text-white text-xs font-bold px-2 py-1 uppercase tracking-wider leading-none">
          Nouveau
        </span>
      @endif
      @if ($hasPromo && $discount > 0)
        <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 uppercase tracking-wider leading-none">
          -{{ $discount }}%
        </span>
      @endif
      @if (!$inStock)
        <span class="bg-gray-700 text-white text-xs font-bold px-2 py-1 uppercase tracking-wider leading-none">
          Épuisé
        </span>
      @endif
    </div>

    {{-- Hover overlay --}}
    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
      <span class="bg-white text-black text-xs font-bold uppercase tracking-wider px-4 py-2 translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
        Voir le produit
      </span>
    </div>

  </a>

  {{-- ── Product Info ── --}}
  <div class="{{ $compact ? 'p-2' : 'p-3 sm:p-4' }} flex-1 flex flex-col">

    {{-- Category --}}
    @if ($categoryName && !$compact)
      <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 truncate">{{ $categoryName }}</p>
    @endif

    {{-- Name --}}
    <a href="{{ route('products.show', $product) }}"
       class="font-semibold text-black hover:text-gray-700 transition-colors truncate {{ $compact ? 'text-xs' : 'text-sm sm:text-base' }} mb-2 block">
      {{ $product->name }}
    </a>

    {{-- Price --}}
    <div class="flex items-baseline gap-2 mt-auto">
      <span class="font-bold {{ $compact ? 'text-sm' : 'text-sm sm:text-base' }} text-black">
        {{ number_format($price, 0, ',', ' ') }} FCFA
      </span>
      @if ($hasPromo)
        <span class="text-xs text-gray-400 line-through">
          {{ number_format($originalPrice, 0, ',', ' ') }} FCFA
        </span>
      @endif
    </div>

  </div>

  {{-- ── Quick Add Button ── --}}
  @if ($showAddToCart && $inStock)
    <button
        @click.prevent="$store.cart.add({
            id:       {{ $product->id }},
            name:     {{ json_encode($product->name) }},
            price:    {{ $price }},
            size:     '',
            color:    '',
            image:    {{ json_encode($image) }},
            quantity: 1
        })"
        class="w-full bg-black hover:bg-accent text-white text-xs font-bold uppercase tracking-wider py-3 transition-colors duration-200 mt-auto">
      <span class="flex items-center justify-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4v16m8-8H4"/>
        </svg>
        Ajouter
      </span>
    </button>
  @elseif ($showAddToCart && !$inStock)
    <div class="w-full bg-gray-100 text-gray-400 text-xs font-bold uppercase tracking-wider py-3 text-center mt-auto cursor-not-allowed">
      Rupture de stock
    </div>
  @endif

</div>
