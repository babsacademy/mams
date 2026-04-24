@extends('layouts.shop')

@section('title', '404 | Page introuvable')
@section('description', 'La page demandée est introuvable. Retournez à l’accueil Prosmax.')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="relative overflow-hidden bg-black text-white min-h-[75vh] flex items-center">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-24 -left-20 h-72 w-72 rounded-full bg-accent/10 blur-3xl"></div>
        <div class="absolute -bottom-28 -right-16 h-80 w-80 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(10,191,188,0.12),transparent_45%),radial-gradient(circle_at_bottom,rgba(255,255,255,0.06),transparent_50%)]"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-20">
        <div class="max-w-3xl">
            <span class="inline-flex items-center rounded-full border border-accent/40 bg-accent/10 px-4 py-1.5 text-[10px] font-bold uppercase tracking-[0.22em] text-accent">
                Erreur 404
            </span>

            <h1 class="mt-6 font-display text-5xl sm:text-6xl lg:text-7xl font-extrabold uppercase tracking-tight leading-[0.92]">
                Page <span class="text-accent">introuvable</span>
            </h1>

            <p class="mt-6 text-base sm:text-lg text-gray-300 max-w-2xl leading-relaxed">
                La page que vous cherchez n’existe plus ou a été déplacée. Continuez votre visite avec les liens ci-dessous.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row gap-4 sm:gap-5">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center justify-center rounded-md bg-white px-8 py-3 text-sm font-bold uppercase tracking-widest text-black hover:bg-gray-200 transition-colors">
                    Retour à l'accueil
                </a>
                <a href="{{ route('catalogue') }}"
                   class="inline-flex items-center justify-center rounded-md border border-white/30 px-8 py-3 text-sm font-bold uppercase tracking-widest text-white hover:bg-white/10 transition-colors">
                    Voir la boutique
                </a>
            </div>

            <div class="mt-10 border-t border-white/10 pt-6 flex flex-wrap items-center gap-x-6 gap-y-3 text-xs uppercase tracking-widest text-gray-400">
                <a href="{{ route('catalogue') }}" class="hover:text-accent transition-colors">Collections</a>
                <a href="{{ route('contact') }}" class="hover:text-accent transition-colors">Contact</a>
                <a href="{{ route('panier') }}" class="hover:text-accent transition-colors">Panier</a>
            </div>
        </div>
    </div>
</section>
@endsection
