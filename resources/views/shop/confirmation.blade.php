@extends('layouts.shop')

@section('title', 'Commande confirmee | ' . ($siteInfo['shop_name'] ?? 'Mams Store World'))
@section('description', 'Votre commande a bien ete enregistree.')

@php
    $whatsapp = $siteInfo['whatsapp_number'] ?? '221771831987';
    $waNumber = ltrim(preg_replace('/[^\d]/', '', $whatsapp), '+');
    $waLink = 'https://wa.me/' . $waNumber . '?text=' . urlencode('Bonjour, je souhaite suivre la commande ' . $order->order_number . '.');
@endphp

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mb-10 text-center">
            <div class="mx-auto grid h-24 w-24 place-items-center rounded-full bg-[#c9a96e] text-black shadow-gold">
                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="label-caps mt-8 text-[#c9a96e]">Commande confirmee</p>
            <h1 class="mt-3 font-display text-4xl sm:text-5xl">Merci {{ explode(' ', $order->customer_name)[0] ?? 'beaucoup' }}</h1>
            <p class="mx-auto mt-4 max-w-xl text-white/70">Votre commande a bien ete enregistree dans le nouveau storefront et sera traitee par l'equipe tres prochainement.</p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-[#111] p-8">
            <div class="flex flex-col gap-6 border-b border-white/10 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="label-caps text-[#c9a96e]">Reference</p>
                    <p class="mt-2 font-display text-3xl">{{ $order->order_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-white/55">Total</p>
                    <p class="mt-2 text-2xl font-semibold">{{ number_format($order->total, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>

            <div class="grid gap-6 border-b border-white/10 py-6 sm:grid-cols-2">
                <div>
                    <p class="label-caps text-[#c9a96e]">Client</p>
                    <p class="mt-3 text-sm text-white/75">{{ $order->customer_name }}</p>
                    <p class="mt-1 text-sm text-white/60">{{ $order->customer_phone }}</p>
                    @if ($order->customer_email)
                        <p class="mt-1 text-sm text-white/60">{{ $order->customer_email }}</p>
                    @endif
                </div>
                <div>
                    <p class="label-caps text-[#c9a96e]">Livraison</p>
                    <p class="mt-3 text-sm text-white/75">{{ $order->delivery_address }}</p>
                    @if ($order->delivery_notes)
                        <p class="mt-1 text-sm text-white/60">{{ $order->delivery_notes }}</p>
                    @endif
                    <p class="mt-1 text-sm text-white/60">Mode de paiement: {{ strtoupper($order->payment_method) }}</p>
                </div>
            </div>

            <div class="py-6">
                <p class="label-caps text-[#c9a96e]">Articles</p>
                <div class="mt-4 space-y-4">
                    @foreach ($order->items as $item)
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-black/20 px-4 py-4">
                            <div class="flex items-center gap-3">
                                @if ($item->product?->image_url)
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="h-14 w-14 rounded-lg object-cover">
                                @endif
                                <div>
                                    <p class="font-medium">{{ $item->product_name }}</p>
                                    <p class="text-sm text-white/55">Quantite: {{ $item->quantity }}</p>
                                </div>
                            </div>
                            <p class="text-sm font-semibold">{{ number_format($item->line_total, 0, ',', ' ') }} FCFA</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="grid h-12 place-items-center rounded-full bg-[#25D366] text-sm font-semibold uppercase tracking-[0.14em] text-black">
                Suivre via WhatsApp
            </a>
            <a href="{{ route('catalogue') }}" class="grid h-12 place-items-center rounded-full border border-white/15 text-sm font-semibold uppercase tracking-[0.14em] text-white">
                Continuer les achats
            </a>
        </div>
    </div>
@endsection
