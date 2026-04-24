@props([
    'product',
    'showAddToCart' => true,
    'compact' => false,
])

@php
    $image = $product->image_url ?: asset('mams-template/assets/images/prod.png');
    $price = $product->price;
    $originalPrice = $product->original_price;
    $hasPromo = $originalPrice && $originalPrice > $price;
    $isNew = (bool) ($product->is_new ?? false);
    $inStock = ($product->stock ?? 0) > 0;
    $categoryName = $product->category?->name;
    $lengthLabel = $product->length_label;
    $colorLabel = $product->color_label;
@endphp

<article class="group rounded-[28px] border border-white/10 bg-[#111] p-3 transition duration-300 hover:-translate-y-1 hover:shadow-gold">
    <a href="{{ route('products.show', $product) }}" class="block">
        <div class="relative overflow-hidden rounded-[22px] border border-white/10 bg-black {{ $compact ? 'aspect-square' : 'aspect-[4/5]' }}">
            <img src="{{ $image }}" alt="{{ $product->name }}" class="h-full w-full object-cover object-center transition duration-500 group-hover:scale-105" loading="lazy">

            <div class="absolute left-3 top-3 flex flex-col gap-2">
                @if ($isNew)
                    <span class="rounded-full bg-[#c9a96e] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-black">Nouveau</span>
                @endif
                @if ($hasPromo)
                    <span class="rounded-full border border-white/20 bg-black/70 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-white">Promo</span>
                @endif
                @if (! $inStock)
                    <span class="rounded-full border border-red-400/40 bg-red-950/50 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-red-200">Rupture</span>
                @endif
            </div>
        </div>
    </a>

    <div class="{{ $compact ? 'px-1 pt-4' : 'px-1 pt-5' }}">
        @if ($categoryName)
            <p class="label-caps text-[#c9a96e]">{{ $categoryName }}</p>
        @endif

        <a href="{{ route('products.show', $product) }}" class="mt-2 block font-display {{ $compact ? 'text-xl' : 'text-2xl' }} leading-tight text-white transition hover:text-[#c9a96e]">
            {{ $product->name }}
        </a>

        @if ($lengthLabel || $colorLabel)
            <div class="mt-3 flex flex-wrap gap-2">
                @if ($lengthLabel)
                    <span class="rounded-full border border-[#c9a96e]/30 bg-[#c9a96e]/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#e7d5b2]">
                        Longueur {{ $lengthLabel }}
                    </span>
                @endif
                @if ($colorLabel)
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-white/75">
                        {{ $colorLabel }}
                    </span>
                @endif
            </div>
        @endif

        <div class="mt-3 flex items-center gap-3">
            <span class="text-lg font-semibold text-white">{{ number_format($price, 0, ',', ' ') }} FCFA</span>
            @if ($hasPromo)
                <span class="text-sm text-white/45 line-through">{{ number_format($originalPrice, 0, ',', ' ') }} FCFA</span>
            @endif
        </div>
    </div>

    @if ($showAddToCart)
        @if ($inStock)
            <button
                type="button"
                @click.prevent="$store.cart.add({
                    id: {{ $product->id }},
                    name: {{ json_encode($product->name) }},
                    price: {{ $price }},
                    size: '',
                    color: '',
                    image: {{ json_encode($image) }},
                    quantity: 1
                })"
                class="mt-5 grid h-11 w-full place-items-center rounded-full bg-[#c9a96e] text-xs font-semibold uppercase tracking-[0.14em] text-black transition hover:brightness-110"
            >
                Ajouter au panier
            </button>
        @else
            <div class="mt-5 grid h-11 w-full place-items-center rounded-full border border-white/10 text-xs font-semibold uppercase tracking-[0.14em] text-white/45">
                Indisponible
            </div>
        @endif
    @endif
</article>
