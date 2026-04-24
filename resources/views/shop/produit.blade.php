@extends('layouts.shop')

@section('title', $product->name . ' | ' . ($siteInfo['shop_name'] ?? 'Mams Store World'))
@section('description', $product->short_description ?? $product->description ?? 'Fiche produit')
@section('og_type', 'product')
@section('og_title', $product->name . ' | ' . ($siteInfo['shop_name'] ?? 'Mams Store World'))
@section('og_description', $product->short_description ?? $product->description ?? 'Fiche produit')
@section('og_image', \App\Models\Setting::resolveMediaUrl($product->image_url) ?: asset('mams-template/assets/images/prod.png'))

@php
    $mainImage = \App\Models\Setting::resolveMediaUrl($product->image_url) ?: asset('mams-template/assets/images/prod.png');
    $galleryItems = collect();

    if ($mainImage) {
        $galleryItems->push([
            'url' => $mainImage,
            'thumbnail_url' => $mainImage,
            'type' => 'image',
            'name' => $product->name,
        ]);
    }

    foreach ($product->media as $media) {
        $galleryItems->push([
            'url' => $media->url,
            'thumbnail_url' => $media->type === 'video' && $media->thumbnail_path ? $media->thumbnail_url : ($media->type === 'image' ? $media->url : null),
            'type' => $media->type,
            'name' => $media->original_name ?: $product->name,
        ]);
    }

    if ($galleryItems->isEmpty()) {
        $galleryItems = collect([
            ['url' => asset('mams-template/assets/images/prod.png'), 'thumbnail_url' => asset('mams-template/assets/images/prod.png'), 'type' => 'image', 'name' => $product->name],
            ['url' => asset('mams-template/assets/images/pr.png'), 'thumbnail_url' => asset('mams-template/assets/images/pr.png'), 'type' => 'image', 'name' => $product->name],
            ['url' => asset('mams-template/assets/images/bifaft.png'), 'thumbnail_url' => asset('mams-template/assets/images/bifaft.png'), 'type' => 'image', 'name' => $product->name],
        ]);
    }

    $whatsAppNumber = ltrim(preg_replace('/[^\d]/', '', $siteInfo['whatsapp_number'] ?? '221771831987'), '+');
    $baseWhatsAppMessage = 'Bonjour ' . ($siteInfo['shop_name'] ?? 'Mams Store World') . ', je souhaite commander ' . $product->name . '.';
@endphp

@section('content')
    <div
        x-data="productPage({
            gallery: @js($galleryItems->values()),
            productId: {{ $product->id }},
            productName: @js($product->name),
            productPrice: {{ (int) $product->price }},
            productImage: @js($mainImage),
            whatsappNumber: @js($whatsAppNumber),
            whatsappMessage: @js($baseWhatsAppMessage),
        })"
        class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-16"
    >
        <div class="grid gap-10 lg:grid-cols-2">
            <div>
                <div class="flex h-[440px] items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-black p-3 md:h-[560px] md:p-4">
                    <template x-if="currentItem.type === 'image'">
                        <img :src="currentItem.url" :alt="currentItem.name" class="max-h-full max-w-full object-contain object-center transition-all duration-300">
                    </template>
                    <template x-if="currentItem.type === 'video'">
                        <video :src="currentItem.url" :poster="currentItem.thumbnail_url || null" controls class="h-full w-full object-cover object-center"></video>
                    </template>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3">
                    <template x-for="(item, index) in gallery" :key="item.url + index">
                        <button
                            type="button"
                            class="h-28 overflow-hidden rounded-xl border transition"
                            :class="activeIndex === index ? 'border-[#c9a96e]' : 'border-white/15'"
                            @click="activeIndex = index"
                        >
                            <template x-if="item.type === 'image'">
                                <img :src="item.thumbnail_url || item.url" :alt="item.name" class="h-full w-full object-cover object-center">
                            </template>
                            <template x-if="item.type === 'video'">
                                <div class="relative h-full w-full overflow-hidden bg-[#151515]">
                                    <template x-if="item.thumbnail_url">
                                        <img :src="item.thumbnail_url" :alt="item.name" class="h-full w-full object-cover object-center">
                                    </template>
                                    <template x-if="! item.thumbnail_url">
                                        <video :src="item.url" muted playsinline preload="metadata" class="h-full w-full object-cover object-center"></video>
                                    </template>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-black/35 text-[#f0d7a0]">
                                        <svg class="h-8 w-8 drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path>
                                        </svg>
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.18em]">Video</span>
                                    </div>
                                </div>
                            </template>
                        </button>
                    </template>
                </div>
            </div>

            <div class="space-y-6">
                @if ($product->category)
                    <a href="{{ route('catalogue', ['category' => $product->category->slug]) }}" class="inline-flex rounded-full border border-[#c9a96e] px-3 py-1 text-[11px] uppercase tracking-[0.16em] text-[#c9a96e]">
                        {{ $product->category->name }}
                    </a>
                @endif

                <h1 class="font-display text-4xl lg:text-5xl">{{ $product->name }}</h1>

                @if ($product->length_label || $product->color_label)
                    <div class="flex flex-wrap gap-2">
                        @if ($product->length_label)
                            <span class="rounded-full border border-[#c9a96e]/30 bg-[#c9a96e]/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#ead7b0]">
                                Longueur {{ $product->length_label }}
                            </span>
                        @endif
                        @if ($product->color_label)
                            <span class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/75">
                                Coloris {{ $product->color_label }}
                            </span>
                        @endif
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <p class="text-2xl font-semibold">{{ number_format($product->price, 0, ',', ' ') }} FCFA</p>
                    @if ($product->original_price && $product->original_price > $product->price)
                        <p class="text-lg text-white/40 line-through">{{ number_format($product->original_price, 0, ',', ' ') }} FCFA</p>
                    @endif
                </div>

                <p class="leading-relaxed text-[#e4dccf]">
                    {{ $product->short_description ?: \Illuminate\Support\Str::limit(strip_tags($product->description), 220) }}
                </p>

                <div class="grid gap-4 rounded-2xl border border-white/10 bg-[#111] p-6 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="label-caps text-[#c9a96e]">Stock</p>
                        <p class="mt-2 text-sm text-white/70">{{ $product->stock > 0 ? 'Disponible' : 'Indisponible' }}</p>
                    </div>
                    @if ($product->length_label)
                        <div>
                            <p class="label-caps text-[#c9a96e]">Longueur / Taille</p>
                            <p class="mt-2 text-sm text-white/70">{{ $product->length_label }}</p>
                        </div>
                    @endif
                    @if ($product->color_label)
                        <div>
                            <p class="label-caps text-[#c9a96e]">Coloris</p>
                            <p class="mt-2 text-sm text-white/70">{{ $product->color_label }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="label-caps text-[#c9a96e]">Livraison</p>
                        <p class="mt-2 text-sm text-white/70">Dakar 24h, regions selon zone</p>
                    </div>
                    <div>
                        <p class="label-caps text-[#c9a96e]">Paiement</p>
                        <p class="mt-2 text-sm text-white/70">Cash, Wave et commandes rapides</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" class="grid h-10 w-10 place-items-center rounded-full border border-white/20" @click="quantity = Math.max(1, quantity - 1)">-</button>
                    <span class="min-w-8 text-center font-semibold" x-text="quantity"></span>
                    <button type="button" class="grid h-10 w-10 place-items-center rounded-full border border-white/20" @click="quantity += 1">+</button>
                </div>

                <div class="space-y-3">
                    @if ($product->stock > 0)
                        <button
                            type="button"
                            class="grid h-12 w-full place-items-center rounded-full bg-[#c9a96e] text-sm font-semibold uppercase tracking-[0.14em] text-black transition hover:brightness-110"
                            @click="$store.cart.add({ id: productId, name: productName, price: productPrice, size: '', color: '', image: productImage, quantity })"
                        >
                            Ajouter au panier
                        </button>
                    @else
                        <div class="grid h-12 w-full place-items-center rounded-full border border-white/10 text-sm font-semibold uppercase tracking-[0.14em] text-white/45">
                            Produit indisponible
                        </div>
                    @endif

                    <a :href="whatsAppLink" target="_blank" rel="noopener noreferrer" class="grid h-12 w-full place-items-center rounded-full bg-[#25D366] text-sm font-semibold uppercase tracking-[0.14em] text-black transition hover:brightness-110">
                        Commander via WhatsApp
                    </a>
                </div>

                <div class="divide-y divide-white/10 rounded-2xl border border-white/10 bg-[#111] px-6">
                    <div class="py-5">
                        <p class="label-caps text-[#c9a96e]">Description</p>
                        <div class="mt-3 text-sm leading-7 text-white/75">
                            {!! nl2br(e($product->description ?: 'Aucune description detaillee n\'est encore renseignee pour ce produit.')) !!}
                        </div>
                    </div>
                    <div class="py-5">
                        <p class="label-caps text-[#c9a96e]">Conseils</p>
                        <p class="mt-3 text-sm leading-7 text-white/75">Utilisez cette fiche pour mettre en avant l'univers client, les qualites du produit et l'accompagnement apres achat.</p>
                    </div>
                </div>
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <section class="pb-4 pt-20">
                <div class="mb-8 flex items-end justify-between gap-4">
                    <div>
                        <p class="label-caps text-[#c9a96e]">Vous aimerez aussi</p>
                        <h2 class="mt-2 font-display text-4xl">Produits similaires</h2>
                    </div>
                    <a href="{{ route('catalogue') }}" class="text-sm uppercase tracking-[0.16em] text-[#d8d1c4] transition hover:text-white">Voir tout</a>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedProducts as $relatedProduct)
                        <x-product-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function productPage(config) {
            return {
                gallery: config.gallery,
                activeIndex: 0,
                quantity: 1,
                productId: config.productId,
                productName: config.productName,
                productPrice: config.productPrice,
                productImage: config.productImage,
                whatsappNumber: config.whatsappNumber,
                whatsappMessage: config.whatsappMessage,

                get currentItem() {
                    return this.gallery[this.activeIndex] || this.gallery[0];
                },

                get whatsAppLink() {
                    const message = `${this.whatsappMessage}\nQuantite: ${this.quantity}\nPrix: ${new Intl.NumberFormat('fr-FR').format(this.productPrice * this.quantity)} FCFA`;

                    return `https://wa.me/${this.whatsappNumber}?text=${encodeURIComponent(message)}`;
                },
            };
        }
    </script>
@endpush
