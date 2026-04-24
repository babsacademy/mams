@php
    $brandName = $siteInfo['shop_name'] ?? 'Mams Store World';
    $whatsapp = $siteInfo['whatsapp_number'] ?? '221771831987';
    $waNumber = ltrim(preg_replace('/[^\d]/', '', $whatsapp), '+');
    $waLink = 'https://wa.me/' . $waNumber;
    $logoUrl = $siteInfo['logo_url'] ?? null;
    $navLinks = [
        ['label' => 'Accueil', 'route' => 'home'],
        ['label' => 'Boutique', 'route' => 'catalogue'],
        ['label' => 'Contact', 'route' => 'contact'],
    ];
@endphp

<header class="sticky top-0 z-50 glass-dark">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-[4.25rem] items-center justify-between gap-4 lg:h-20">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                @if ($logoUrl)
                    <span class="flex h-10 w-[138px] items-center justify-center sm:h-11 sm:w-[168px] lg:h-12 lg:w-[200px]">
                        <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-full w-full object-contain object-center">
                    </span>
                @else
                    <span class="font-display text-3xl text-[#c9a96e]">{{ $brandName }}</span>
                @endif
            </a>

            <nav class="hidden md:flex items-center gap-8">
                @foreach ($navLinks as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        class="mams-link text-sm uppercase tracking-[0.18em] transition {{ Request::routeIs($link['route']) ? 'text-[#c9a96e]' : 'text-[#f5f0e8] hover:text-[#c9a96e]' }}"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden md:flex items-center gap-4">
                <a
                    href="{{ $waLink }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex h-11 items-center rounded-full border border-[#c9a96e]/40 px-5 text-xs font-semibold uppercase tracking-[0.14em] text-[#c9a96e] transition hover:bg-[#c9a96e] hover:text-black"
                >
                    WhatsApp
                </a>
                <button
                    type="button"
                    @click="$store.cart.open()"
                    class="relative grid h-11 w-11 place-items-center rounded-full border border-white/15 text-[#f5f0e8] transition hover:border-[#c9a96e] hover:text-[#c9a96e]"
                    aria-label="Ouvrir le panier"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span
                        x-data
                        x-show="$store.cart.count > 0"
                        x-text="$store.cart.count"
                        class="absolute -right-1 -top-1 min-w-5 rounded-full bg-[#c9a96e] px-1 text-center text-[10px] font-semibold text-black"
                        x-cloak
                    ></span>
                </button>
            </div>

            {{-- Mobile: panier + hamburger --}}
            <div class="flex items-center gap-2 md:hidden">
                <button
                    type="button"
                    @click="$store.cart.open()"
                    class="relative grid h-11 w-11 place-items-center rounded-full border border-white/15 text-[#f5f0e8]"
                    aria-label="Ouvrir le panier"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span
                        x-data
                        x-show="$store.cart.count > 0"
                        x-text="$store.cart.count"
                        class="absolute -right-1 -top-1 min-w-5 rounded-full bg-[#c9a96e] px-1 text-center text-[10px] font-semibold text-black"
                        x-cloak
                    ></span>
                </button>
                <button
                    type="button"
                    @click="mobileMenuOpen = true"
                    class="grid h-11 w-11 place-items-center rounded-full border border-white/15 text-[#f5f0e8]"
                    aria-label="Ouvrir le menu"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>

{{-- =============================================
     MOBILE MENU — plein écran, en dehors du <header>
     z-index inline pour éviter les problèmes CDN
     ============================================= --}}
<div
    x-show="mobileMenuOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 flex flex-col md:hidden"
    style="z-index: 200; background-color: #0a0a0a;"
    x-cloak
>
    {{-- Top bar --}}
    <div class="flex items-center justify-between px-6 py-5" style="border-bottom: 1px solid rgba(255,255,255,0.07);">
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-9 w-auto object-contain">
        @else
            <span class="font-display text-2xl text-[#c9a96e]">{{ $brandName }}</span>
        @endif
        <button
            type="button"
            @click="mobileMenuOpen = false"
            class="grid h-10 w-10 place-items-center rounded-full text-white/50 transition hover:text-white"
            style="border: 1px solid rgba(255,255,255,0.12);"
            aria-label="Fermer"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Nav links --}}
    <nav class="flex flex-1 flex-col justify-center px-8 gap-2">
        @foreach ($navLinks as $link)
            <a
                href="{{ route($link['route']) }}"
                @click="mobileMenuOpen = false"
                class="flex items-center justify-between py-5 transition-colors"
                style="border-bottom: 1px solid rgba(255,255,255,0.06);"
            >
                <span class="font-display text-4xl {{ Request::routeIs($link['route']) ? 'text-[#c9a96e]' : 'text-white' }}">
                    {{ $link['label'] }}
                </span>
                <svg class="h-5 w-5 {{ Request::routeIs($link['route']) ? 'text-[#c9a96e]' : 'text-white/20' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @endforeach
    </nav>

    {{-- Bottom actions --}}
    <div class="px-8 pb-10 pt-6 space-y-3">
        <a
            href="{{ $waLink }}"
            target="_blank"
            rel="noopener noreferrer"
            @click="mobileMenuOpen = false"
            class="flex w-full items-center justify-center gap-3 rounded-full py-4 text-sm font-semibold uppercase tracking-[0.14em] text-black"
            style="background-color: #c9a96e;"
        >
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            WhatsApp
        </a>
        <button
            type="button"
            @click="mobileMenuOpen = false; $nextTick(() => $store.cart.open())"
            class="flex w-full items-center justify-center gap-3 rounded-full py-4 text-sm font-semibold uppercase tracking-[0.14em] text-white transition"
            style="border: 1px solid rgba(255,255,255,0.15);"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span x-data x-text="$store.cart.count > 0 ? 'Mon panier (' + $store.cart.count + ')' : 'Mon panier'"></span>
        </button>
    </div>
</div>

{{-- ==================
     PANIER LATÉRAL
     ================== --}}
<div x-data x-show="$store.cart.isOpen" class="relative z-[70]" x-cloak>
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="$store.cart.close()"></div>
    <div class="fixed inset-y-0 right-0 flex max-w-full pl-6">
        <aside
            x-show="$store.cart.isOpen"
            x-transition:enter="transform transition ease-in-out duration-500"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-500"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="flex h-full w-screen max-w-md flex-col border-l border-white/10 bg-[#101010] shadow-2xl"
        >
            <div class="flex items-center justify-between border-b border-white/10 px-6 py-5">
                <div>
                    <p class="label-caps text-[#c9a96e]">Panier</p>
                    <p class="mt-1 text-sm text-white/60">Votre selection du moment</p>
                </div>
                <button
                    type="button"
                    class="grid h-10 w-10 place-items-center rounded-full border border-white/15 text-white/70 transition hover:text-white"
                    @click="$store.cart.close()"
                    aria-label="Fermer le panier"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-6">
                <template x-if="$store.cart.items.length === 0">
                    <div class="rounded-2xl border border-white/10 bg-[#151515] p-8 text-center">
                        <div class="mx-auto grid h-16 w-16 place-items-center rounded-full border border-white/15 text-[#c9a96e]">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <p class="mt-5 font-display text-3xl">Panier vide</p>
                        <p class="mt-2 text-sm text-white/60">Ajoutez vos pieces favorites pour preparer la commande.</p>
                        <a
                            href="{{ route('catalogue') }}"
                            class="mt-6 inline-flex h-11 items-center rounded-full bg-[#c9a96e] px-6 text-xs font-semibold uppercase tracking-[0.14em] text-black"
                            @click="$store.cart.close()"
                        >
                            Retour boutique
                        </a>
                    </div>
                </template>

                <template x-for="item in $store.cart.items" :key="item.id + '-' + item.size + '-' + item.color">
                    <article class="flex gap-4 rounded-2xl border border-white/10 bg-[#151515] p-4">
                        <div class="h-24 w-20 overflow-hidden rounded-xl bg-black/50">
                            <img :src="item.image" :alt="item.name" class="h-full w-full object-cover">
                        </div>
                        <div class="flex flex-1 flex-col justify-between gap-3">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-display text-2xl leading-none" x-text="item.name"></h3>
                                    <p class="text-sm font-semibold text-[#c9a96e]" x-text="new Intl.NumberFormat('fr-FR').format(item.price * item.quantity) + ' FCFA'"></p>
                                </div>
                                <template x-if="item.size || item.color">
                                    <p class="mt-2 text-xs text-white/55" x-text="[item.size, item.color].filter(Boolean).join(' · ')"></p>
                                </template>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <button type="button" class="grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/70" @click="$store.cart.updateQuantity(item.id, item.size, item.color, -1)">-</button>
                                    <span class="min-w-6 text-center text-sm font-semibold" x-text="item.quantity"></span>
                                    <button type="button" class="grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/70" @click="$store.cart.updateQuantity(item.id, item.size, item.color, 1)">+</button>
                                </div>
                                <button type="button" class="text-xs uppercase tracking-[0.14em] text-white/50 transition hover:text-white" @click="$store.cart.remove(item.id, item.size, item.color)">
                                    Supprimer
                                </button>
                            </div>
                        </div>
                    </article>
                </template>
            </div>

            <div class="border-t border-white/10 bg-black/30 px-6 py-6" x-show="$store.cart.items.length > 0">
                <div class="flex items-center justify-between text-sm">
                    <span class="label-caps text-white/60">Sous-total</span>
                    <span class="text-lg font-semibold text-[#c9a96e]" x-text="new Intl.NumberFormat('fr-FR').format($store.cart.total) + ' FCFA'"></span>
                </div>
                <div class="mt-5 grid gap-3">
                    <a href="{{ route('panier') }}" class="grid h-11 place-items-center rounded-full border border-white/15 text-xs font-semibold uppercase tracking-[0.14em] text-white" @click="$store.cart.close()">Voir le panier</a>
                    <a href="{{ route('checkout') }}" class="grid h-11 place-items-center rounded-full bg-[#c9a96e] text-xs font-semibold uppercase tracking-[0.14em] text-black" @click="$store.cart.close()">Commander</a>
                </div>
            </div>
        </aside>
    </div>
</div>
