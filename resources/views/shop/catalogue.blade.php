@extends('layouts.shop')

@section('title', 'Boutique | ' . ($siteInfo['shop_name'] ?? 'Mams Store World'))
@section('description', 'Explorez la collection complete dans le nouveau storefront Mams.')

@section('content')
    <div x-data="cataloguePage()" x-init="init()" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <section class="pt-14 pb-10 text-center">
            <p class="label-caps text-[#c9a96e]">Collection signature</p>
            <h1 class="mt-3 font-display text-5xl">Notre Collection</h1>
            <p class="mx-auto mt-4 max-w-2xl text-white/70">Le catalogue garde les donnees Laravel existantes mais adopte maintenant l'identite Mams sur toute la vitrine.</p>
        </section>

        <section class="sticky top-24 z-30 mb-8 rounded-2xl border border-white/10 bg-[#101010]/95 px-4 py-5 backdrop-blur">
            <div class="grid gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('catalogue') }}" class="rounded-full border px-4 py-2 text-xs uppercase tracking-[0.12em] {{ ! request('category') ? 'border-[#c9a96e] bg-[#c9a96e] text-black' : 'border-white/15 text-white/70 hover:border-[#c9a96e] hover:text-white' }}">
                        Toutes
                    </a>
                    @foreach ($categories as $category)
                        <a
                            href="{{ route('catalogue', ['category' => $category->slug]) }}"
                            class="rounded-full border px-4 py-2 text-xs uppercase tracking-[0.12em] {{ request('category') === $category->slug ? 'border-[#c9a96e] bg-[#c9a96e] text-black' : 'border-white/15 text-white/70 hover:border-[#c9a96e] hover:text-white' }}"
                        >
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>

                <div class="grid gap-4 lg:grid-cols-[1fr_auto_auto]">
                    <div class="flex items-center rounded-full border border-white/15 px-4">
                        <svg class="h-4 w-4 text-[#c9a96e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="text"
                            x-model="search"
                            placeholder="Rechercher un produit..."
                            class="h-11 w-full bg-transparent px-3 text-sm outline-none"
                        >
                    </div>

                    <div class="flex items-center text-sm text-white/60">
                        <span x-text="filteredCount"></span>&nbsp;resultat<span x-show="filteredCount !== 1">s</span>
                    </div>

                    <select x-model="sort" class="h-11 rounded-full border border-white/15 bg-transparent px-4 text-sm outline-none">
                        <option value="newest" class="text-black">Nouveautes</option>
                        <option value="name_asc" class="text-black">Nom A-Z</option>
                        <option value="name_desc" class="text-black">Nom Z-A</option>
                        <option value="price_asc" class="text-black">Prix croissant</option>
                        <option value="price_desc" class="text-black">Prix decroissant</option>
                    </select>
                </div>
            </div>
        </section>

        @if ($products->count() > 0)
            <section class="grid grid-cols-1 gap-6 pb-8 sm:grid-cols-2 lg:grid-cols-3" x-ref="grid">
                @foreach ($products as $product)
                    <div
                        data-product-card
                        data-name="{{ strtolower($product->name) }}"
                        data-category="{{ strtolower($product->category?->name ?? '') }}"
                        data-meta="{{ strtolower(trim(($product->length_label ?? '') . ' ' . ($product->color_label ?? ''))) }}"
                        data-price="{{ $product->price }}"
                        data-date="{{ $product->created_at?->timestamp ?? 0 }}"
                    >
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </section>

            <div x-show="filteredCount === 0" class="rounded-[28px] border border-white/10 bg-[#111] p-12 text-center" x-cloak>
                <p class="font-display text-4xl">Aucun resultat</p>
                <p class="mt-3 text-white/65">Essayez une autre recherche ou revenez a toute la collection.</p>
                <button type="button" class="mt-6 inline-flex h-11 items-center rounded-full bg-[#c9a96e] px-6 text-xs font-semibold uppercase tracking-[0.14em] text-black" @click="search = ''">
                    Reinitialiser
                </button>
            </div>
        @else
            <section class="rounded-[28px] border border-white/10 bg-[#111] p-12 text-center">
                <p class="font-display text-4xl">Catalogue vide</p>
                <p class="mt-3 text-white/65">Ajoutez des produits dans l'admin pour alimenter cette vitrine.</p>
                <a href="{{ route('home') }}" class="mt-6 inline-flex h-11 items-center rounded-full bg-[#c9a96e] px-6 text-xs font-semibold uppercase tracking-[0.14em] text-black">
                    Retour accueil
                </a>
            </section>
        @endif

        @if ($products->hasPages())
            <div class="pb-16 pt-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function cataloguePage() {
            return {
                search: '',
                sort: 'newest',
                filteredCount: 0,
                cards: [],

                init() {
                    this.cards = Array.from(this.$root.querySelectorAll('[data-product-card]'));
                    this.filteredCount = this.cards.length;

                    this.$watch('search', () => this.refresh());
                    this.$watch('sort', () => this.refresh());

                    this.refresh();
                },

                refresh() {
                    const query = this.search.trim().toLowerCase();
                    const visibleCards = this.cards.filter((card) => {
                        const name = card.dataset.name || '';
                        const category = card.dataset.category || '';
                        const meta = card.dataset.meta || '';
                        const visible = ! query || name.includes(query) || category.includes(query) || meta.includes(query);
                        card.style.display = visible ? '' : 'none';

                        return visible;
                    });

                    this.filteredCount = visibleCards.length;

                    const sortedCards = [...this.cards].sort((first, second) => {
                        switch (this.sort) {
                            case 'name_asc':
                                return (first.dataset.name || '').localeCompare(second.dataset.name || '');
                            case 'name_desc':
                                return (second.dataset.name || '').localeCompare(first.dataset.name || '');
                            case 'price_asc':
                                return Number(first.dataset.price || 0) - Number(second.dataset.price || 0);
                            case 'price_desc':
                                return Number(second.dataset.price || 0) - Number(first.dataset.price || 0);
                            default:
                                return Number(second.dataset.date || 0) - Number(first.dataset.date || 0);
                        }
                    });

                    sortedCards.forEach((card) => {
                        this.$refs.grid.appendChild(card);
                    });
                },
            };
        }
    </script>
@endpush
