#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Write the improved storefront index blade file."""

import os

filepath = os.path.join(
    "c:", os.sep, "dev", "schic", "resources", "views", "components",
    "admin", "storefront", "\u26a1index.blade.php"
)

content = """\
<?php
use App\\Models\\Setting;
use Livewire\\Attributes\\Layout;
use Livewire\\Attributes\\Title;
use Livewire\\Component;

new #[Title('Vitrine')] #[Layout('layouts.app')] class extends Component
{
    // Hero
    public string $heroImageUrl = '';
    public string $heroBadge = '';
    public string $heroTitleLine1 = '';
    public string $heroTitleLine2 = '';
    public string $heroDescription = '';
    public string $heroCta1Text = '';
    public string $heroCta2Text = '';

    public string $successMessage = '';

    public function mount(): void
    {
        $this->heroImageUrl    = Setting::get('hero_image_url', 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?q=80&w=2000');
        $this->heroBadge       = Setting::get('hero_badge', 'Collection 2024');
        $this->heroTitleLine1  = Setting::get('hero_title_line1', 'Le cuir');
        $this->heroTitleLine2  = Setting::get('hero_title_line2', 'r\u00e9invent\u00e9.');
        $this->heroDescription = Setting::get('hero_description', "Maroquinerie d'exception fa\u00e7onn\u00e9e \u00e0 la main dans notre atelier de Dakar. Chaque pi\u00e8ce incarne le raffinement et la durabilit\u00e9 du cuir premium s\u00e9n\u00e9galais.");
        $this->heroCta1Text    = Setting::get('hero_cta1_text', 'D\u00e9couvrir la boutique');
        $this->heroCta2Text    = Setting::get('hero_cta2_text', 'Notre savoir-faire');
    }

    public function saveHero(): void
    {
        $this->validate([
            'heroImageUrl'    => ['nullable', 'string', 'max:500'],
            'heroBadge'       => ['nullable', 'string', 'max:60'],
            'heroTitleLine1'  => ['required', 'string', 'max:80'],
            'heroTitleLine2'  => ['required', 'string', 'max:80'],
            'heroDescription' => ['nullable', 'string', 'max:400'],
            'heroCta1Text'    => ['nullable', 'string', 'max:60'],
            'heroCta2Text'    => ['nullable', 'string', 'max:60'],
        ]);

        Setting::set('hero_image_url', $this->heroImageUrl, 'hero');
        Setting::set('hero_badge', $this->heroBadge, 'hero');
        Setting::set('hero_title_line1', $this->heroTitleLine1, 'hero');
        Setting::set('hero_title_line2', $this->heroTitleLine2, 'hero');
        Setting::set('hero_description', $this->heroDescription, 'hero');
        Setting::set('hero_cta1_text', $this->heroCta1Text, 'hero');
        Setting::set('hero_cta2_text', $this->heroCta2Text, 'hero');

        $this->successMessage = 'Vitrine mise \u00e0 jour avec succ\u00e8s.';
    }
}; ?>

<div class="space-y-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Vitrine</h1>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Personnalisez l'apparence de votre page d'accueil.</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-2 text-[10px] font-black text-zinc-400 uppercase tracking-widest hover:text-brand-pink transition-colors">
            <flux:icon.eye class="size-4" />
            Voir le r\u00e9sultat en direct
        </a>
    </div>

    @if($successMessage)
        <div class="fixed top-8 right-8 z-[60] flex items-center gap-3 rounded-2xl bg-emerald-500 text-white px-6 py-4 shadow-2xl shadow-emerald-500/20 animate-in slide-in-from-right-10 duration-500"
             x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <div class="size-6 bg-white/20 rounded-full flex items-center justify-center">
                <flux:icon.check class="size-4" />
            </div>
            <p class="text-sm font-black uppercase tracking-widest">{{ $successMessage }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-10 items-start">
        {{-- Aper\u00e7u live --}}
        <div class="space-y-8">
            <div class="rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-2xl relative h-[28rem] bg-zinc-900 group">
                @if($heroImageUrl)
                    <img src="{{ $heroImageUrl }}" alt="Aper\u00e7u hero" class="w-full h-full object-cover opacity-60 transition-transform duration-1000 group-hover:scale-110">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-12">
                    @if($heroBadge)
                        <div class="w-fit px-3 py-1 bg-brand-pink/20 backdrop-blur-md rounded-full border border-brand-pink/30 mb-4 animate-in fade-in slide-in-from-bottom-4">
                            <span class="text-brand-pink text-[9px] uppercase tracking-[0.4em] font-black">{{ $heroBadge }}</span>
                        </div>
                    @endif
                    <h2 class="text-white font-black uppercase leading-[1.1] text-5xl tracking-tighter mb-4">
                        {{ $heroTitleLine1 }}<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-pink via-rose-300 to-white">{{ $heroTitleLine2 }}</span>
                    </h2>
                    @if($heroDescription)
                        <p class="text-white/60 text-sm max-w-sm line-clamp-3 leading-relaxed font-medium">{{ $heroDescription }}</p>
                    @endif

                    <div class="flex gap-4 mt-8">
                        <div class="h-10 px-6 bg-brand-pink rounded-xl flex items-center justify-center text-[10px] font-black text-white uppercase tracking-widest">{{ $heroCta1Text ?: 'Bouton 1' }}</div>
                        <div class="h-10 px-6 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl flex items-center justify-center text-[10px] font-black text-white uppercase tracking-widest">{{ $heroCta2Text ?: 'Bouton 2' }}</div>
                    </div>
                </div>
                <div class="absolute top-8 right-8">
                    <span class="bg-black/60 backdrop-blur-md border border-white/10 text-white/80 text-[10px] uppercase tracking-[0.2em] px-4 py-2 rounded-xl font-black">Aper\u00e7u en temps r\u00e9el</span>
                </div>
            </div>
        </div>

        {{-- Formulaire --}}
        <div class="space-y-8">
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-8">
                <div class="flex items-center gap-3">
                    <div class="size-9 bg-zinc-50 dark:bg-zinc-800 rounded-xl flex items-center justify-center">
                        <flux:icon.photo class="size-5 text-zinc-400" />
                    </div>
                    <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Visuel Principal</h3>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">URL de l'image (Haute D\u00e9finition recommand\u00e9e)</label>
                        <flux:input wire:model.live="heroImageUrl" variant="filled" placeholder="https://..." class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                    </div>
                </div>

                <div class="space-y-4 border-t border-zinc-100 dark:border-zinc-800/50 pt-8">
                    <div class="flex items-center gap-3">
                        <div class="size-9 bg-zinc-50 dark:bg-zinc-800 rounded-xl flex items-center justify-center">
                            <flux:icon.pencil-square class="size-5 text-zinc-400" />
                        </div>
                        <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">R\u00e9dactionnel</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Badge de mise en avant</label>
                            <flux:input wire:model.live="heroBadge" variant="filled" placeholder="Ex: Collection 2024" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Titre - Ligne 1</label>
                            <flux:input wire:model.live="heroTitleLine1" variant="filled" placeholder="Ex: Le luxe" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Titre - Ligne 2 <span class="text-brand-pink">(Couleur)</span></label>
                            <flux:input wire:model.live="heroTitleLine2" variant="filled" placeholder="Ex: \u00e0 port\u00e9e" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Description d'introduction</label>
                            <flux:textarea wire:model.live="heroDescription" variant="filled" rows="4" placeholder="\u00c9vocation de l'univers de votre marque..." class="!bg-zinc-50 dark:!bg-zinc-800 font-bold" />
                        </div>
                    </div>
                </div>

                <div class="space-y-4 border-t border-zinc-100 dark:border-zinc-800/50 pt-8">
                    <div class="flex items-center gap-3">
                        <div class="size-9 bg-zinc-50 dark:bg-zinc-800 rounded-xl flex items-center justify-center">
                            <flux:icon.cursor-arrow-rays class="size-5 text-zinc-400" />
                        </div>
                        <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Actions</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Texte Bouton 1</label>
                            <flux:input wire:model.live="heroCta1Text" variant="filled" placeholder="Ex: D\u00e9couvrir" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Texte Bouton 2</label>
                            <flux:input wire:model.live="heroCta2Text" variant="filled" placeholder="Ex: Concept" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <flux:button wire:click="saveHero" variant="primary" class="!bg-brand-pink border-none font-black uppercase tracking-widest text-[10px] py-4 w-full rounded-2xl shadow-xl shadow-brand-pink/20">
                        Sauvegarder la vitrine
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
"""

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print(f"Written to: {filepath}")
print(f"File size: {os.path.getsize(filepath)} bytes")
