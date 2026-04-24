@extends('layouts.shop')

@section('title', 'Panier | ' . ($siteInfo['shop_name'] ?? 'Mams Store World'))
@section('description', 'Consultez votre panier avant de passer a la commande.')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="mb-10 font-display text-5xl">Mon Panier</h1>

        <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
            <section>
                <div x-data x-show="$store.cart.items.length === 0" class="rounded-2xl border border-white/10 bg-[#111] p-10 text-center" x-cloak>
                    <div class="mx-auto grid h-24 w-24 place-items-center rounded-full border border-white/15 text-[#c9a96e]">
                        <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h2 class="mt-5 font-display text-3xl">Panier vide</h2>
                    <p class="mt-3 text-white/70">Ajoutez vos produits favoris pour commencer votre commande.</p>
                    <a href="{{ route('catalogue') }}" class="mt-6 inline-flex h-11 items-center rounded-full bg-[#c9a96e] px-6 text-xs font-semibold uppercase tracking-[0.14em] text-black">
                        Retour boutique
                    </a>
                </div>

                <div x-data x-show="$store.cart.items.length > 0" class="overflow-x-auto rounded-2xl border border-white/10" x-cloak>
                    <table class="min-w-full text-sm">
                        <thead class="bg-[#151515] text-[#c9a96e] label-caps">
                            <tr>
                                <th class="px-4 py-4 text-left">Produit</th>
                                <th class="px-4 py-4 text-left">Prix</th>
                                <th class="px-4 py-4 text-left">Quantite</th>
                                <th class="px-4 py-4 text-left">Total</th>
                                <th class="px-4 py-4 text-left">Supprimer</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-[#101010]">
                            <template x-for="item in $store.cart.items" :key="item.id + '-' + item.size + '-' + item.color">
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="flex min-w-[260px] items-center gap-3">
                                            <div class="h-16 w-16 overflow-hidden rounded-xl bg-black/50 shrink-0">
                                                <img :src="item.image" :alt="item.name" class="h-full w-full object-cover">
                                            </div>
                                            <div>
                                                <p class="font-semibold" x-text="item.name"></p>
                                                <template x-if="item.size || item.color">
                                                    <p class="text-xs text-white/60" x-text="[item.size, item.color].filter(Boolean).join(' · ')"></p>
                                                </template>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4" x-text="new Intl.NumberFormat('fr-FR').format(item.price) + ' FCFA'"></td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <button type="button" class="grid h-8 w-8 place-items-center rounded-full border border-white/20" @click="$store.cart.updateQuantity(item.id, item.size, item.color, -1)">-</button>
                                            <span class="min-w-7 text-center" x-text="item.quantity"></span>
                                            <button type="button" class="grid h-8 w-8 place-items-center rounded-full border border-white/20" @click="$store.cart.updateQuantity(item.id, item.size, item.color, 1)">+</button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4" x-text="new Intl.NumberFormat('fr-FR').format(item.price * item.quantity) + ' FCFA'"></td>
                                    <td class="px-4 py-4">
                                        <button type="button" class="text-red-300 transition hover:text-red-200" @click="$store.cart.remove(item.id, item.size, item.color)">
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>

            <aside x-data class="h-fit rounded-2xl border border-white/10 bg-[#111] p-6 lg:sticky lg:top-24">
                <h2 class="font-display text-3xl">Resume</h2>
                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between"><span>Sous-total</span><span x-text="new Intl.NumberFormat('fr-FR').format($store.cart.total) + ' FCFA'"></span></div>
                    <div class="flex justify-between"><span>Livraison</span><span>Selon zone</span></div>
                    <div class="flex justify-between border-t border-white/10 pt-3 text-lg font-semibold">
                        <span>Total</span>
                        <span x-text="new Intl.NumberFormat('fr-FR').format($store.cart.total) + ' FCFA'"></span>
                    </div>
                </div>

                <a href="{{ route('checkout') }}" class="mt-5 grid h-12 w-full place-items-center rounded-full bg-[#c9a96e] text-xs font-semibold uppercase tracking-[0.14em] text-black">
                    Passer la commande
                </a>
            </aside>
        </div>
    </div>
@endsection
