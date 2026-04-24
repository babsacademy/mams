@extends('layouts.shop')

@section('title', ($siteInfo['shop_name'] ?? 'Mams Store World') . ' | Hair, beauty, style')
@section('description', $heroDescription)

@section('content')
    <section class="relative min-h-screen overflow-hidden bg-mams-hero">
        <img
            src="{{ $heroImageUrl }}"
            alt="{{ $heroTitleLine1 }} {{ $heroTitleLine2 }}"
            class="absolute inset-0 h-full w-full object-cover object-center"
            style="object-position: {{ $heroImagePositionX }}% {{ $heroImagePositionY }}%;"
        >
        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/55 to-black/70"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_45%,rgba(201,169,110,0.22),transparent_52%)]"></div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl items-center px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl py-24">
                <p class="label-caps mb-6 text-[#c9a96e]">{{ $heroBadge }}</p>
                <h1 class="font-display text-5xl leading-[0.95] sm:text-6xl lg:text-7xl">
                    {{ $heroTitleLine1 }}<br>{{ $heroTitleLine2 }}
                </h1>
                <p class="mt-5 max-w-2xl text-lg text-[#f5f0e8] sm:text-xl">{{ $heroDescription }}</p>
                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('catalogue') }}" class="inline-flex h-12 items-center justify-center rounded-full bg-[#c9a96e] px-7 text-xs font-semibold uppercase tracking-[0.16em] text-black transition hover:brightness-110">
                        {{ $heroCta1Text }}
                    </a>
                    <a href="#why-us" class="inline-flex h-12 items-center justify-center rounded-full border border-white/20 px-7 text-xs font-semibold uppercase tracking-[0.16em] text-white transition hover:border-[#c9a96e] hover:text-[#c9a96e]">
                        {{ $heroCta2Text }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-end justify-between gap-4">
            <div>
                <p class="label-caps text-[#c9a96e]">Selection</p>
                <h2 class="mt-2 font-display text-4xl">Nouveautes</h2>
            </div>
            <a href="{{ route('catalogue') }}" class="text-sm uppercase tracking-[0.16em] text-[#d8d1c4] transition hover:text-white">Voir tout</a>
        </div>

        @if ($newArrivals->isNotEmpty())
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($newArrivals->take(3) as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @else
            <div class="rounded-[28px] border border-white/10 bg-[#111] p-10 text-center text-white/65">
                Les nouveautes arriveront bientot dans la boutique.
            </div>
        @endif
    </section>

    <section class="marquee bg-black py-4">
        <div class="marquee-track label-caps text-[#c9a96e]">
            <span class="marquee-segment">PREMIUM QUALITY · BEAUTY ESSENTIALS · SIGNATURE LOOKS · DELIVERY FAST ·</span>
            <span class="marquee-segment" aria-hidden="true">PREMIUM QUALITY · BEAUTY ESSENTIALS · SIGNATURE LOOKS · DELIVERY FAST ·</span>
            <span class="marquee-segment" aria-hidden="true">PREMIUM QUALITY · BEAUTY ESSENTIALS · SIGNATURE LOOKS · DELIVERY FAST ·</span>
            <span>PREMIUM QUALITY · BEAUTY ESSENTIALS · SIGNATURE LOOKS · DELIVERY FAST ·</span>
            <span>PREMIUM QUALITY · BEAUTY ESSENTIALS · SIGNATURE LOOKS · DELIVERY FAST ·</span>
        </div>
    </section>

    <section id="why-us" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="label-caps text-[#c9a96e]">Pourquoi nous</p>
            <h2 class="mt-2 font-display text-4xl">Pourquoi {{ $siteInfo['shop_name'] ?? 'Mams Store World' }}</h2>
        </div>

        <div class="mt-12 grid gap-6 md:grid-cols-3">
            <article class="hover-lift rounded-2xl border border-white/10 bg-[#111] p-7 text-center">
                <p class="text-4xl text-[#c9a96e]">01</p>
                <h3 class="mt-4 font-display text-2xl">Qualite premium</h3>
                <p class="mt-3 text-white/70">Une presentation luxe, des visuels forts et une experience plus haut de gamme sur tout le storefront.</p>
            </article>
            <article class="hover-lift rounded-2xl border border-white/10 bg-[#111] p-7 text-center">
                <p class="text-4xl text-[#c9a96e]">02</p>
                <h3 class="mt-4 font-display text-2xl">Navigation rapide</h3>
                <p class="mt-3 text-white/70">Catalogue, panier et checkout ont ete refaits pour rester fluides sur mobile comme sur desktop.</p>
            </article>
            <article class="hover-lift rounded-2xl border border-white/10 bg-[#111] p-7 text-center">
                <p class="text-4xl text-[#c9a96e]">03</p>
                <h3 class="mt-4 font-display text-2xl">Conversion focus</h3>
                <p class="mt-3 text-white/70">Les CTA, le panier lateral et le checkout gardent la logique Laravel actuelle tout en adoptant le template Mams.</p>
            </article>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="h-[420px] overflow-hidden rounded-2xl border border-white/10 lg:h-[520px]">
                <img src="{{ asset('mams-template/assets/images/prod.png') }}" alt="Editorial beauty" class="h-full w-full object-cover object-center">
            </div>
            <div class="grid gap-6">
                <div class="h-[250px] overflow-hidden rounded-2xl border border-white/10">
                    <img src="{{ asset('mams-template/assets/images/pr.png') }}" alt="Lifestyle beauty" class="h-full w-full object-cover object-center">
                </div>
                <div class="flex flex-col justify-center rounded-2xl border border-[#c9a96e]/30 bg-[#151515] p-8">
                    <p class="label-caps text-[#c9a96e]">Collections</p>
                    <h3 class="mt-3 font-display text-4xl">Un storefront adapte a un autre client</h3>
                    <p class="mt-4 text-white/70">Le front public reprend maintenant le langage visuel du template Mams tout en gardant votre moteur Laravel pour les produits, commandes et contenus.</p>
                    <a href="{{ route('catalogue') }}" class="mt-5 text-sm uppercase tracking-[0.16em] text-[#d8d1c4] transition hover:text-white">Explorer la boutique</a>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="mb-10 text-center">
            <p class="label-caps text-[#c9a96e]">Collections</p>
            <h2 class="mt-2 font-display text-4xl">Explorer par categorie</h2>
        </div>

        @if ($categories->isNotEmpty())
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($categories->take(3) as $category)
                    @php
                        $categoryImage = \App\Models\Setting::resolveMediaUrl($category->image_url) ?? asset('mams-template/assets/images/bifaft.png');
                    @endphp
                    <a href="{{ route('catalogue', ['category' => $category->slug]) }}" class="hover-lift overflow-hidden rounded-[28px] border border-white/10 bg-[#111]">
                        <div class="h-72 overflow-hidden border-b border-white/10">
                            <img src="{{ $categoryImage }}" alt="{{ $category->name }}" class="h-full w-full object-cover object-center transition duration-500 hover:scale-105">
                        </div>
                        <div class="p-6">
                            <p class="label-caps text-[#c9a96e]">Categorie</p>
                            <h3 class="mt-3 font-display text-3xl">{{ $category->name }}</h3>
                            <p class="mt-3 text-white/70">{{ $category->products_count }} produit{{ $category->products_count > 1 ? 's' : '' }} actif{{ $category->products_count > 1 ? 's' : '' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-[28px] border border-white/10 bg-[#111] p-10 text-center text-white/65">
                Aucune categorie n'est encore visible.
            </div>
        @endif
    </section>

    @if ($featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <p class="label-caps text-[#c9a96e]">Best sellers</p>
                    <h2 class="mt-2 font-display text-4xl">Selection signature</h2>
                </div>
                <a href="{{ route('catalogue') }}" class="text-sm uppercase tracking-[0.16em] text-[#d8d1c4] transition hover:text-white">Voir toute la collection</a>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featuredProducts->take(4) as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($productsWithVideos->isNotEmpty())
        <section class="border-y border-[#c9a96e]/30 bg-black py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-center label-caps text-[#c9a96e]">Produits avec medias video disponibles · Experience premium integree au storefront</p>
            </div>
        </section>
    @endif
@endsection
