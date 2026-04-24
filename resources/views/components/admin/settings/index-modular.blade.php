<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Paramètres')] #[Layout('layouts.app')] class extends Component {
    public string $tab = 'boutique';

    public function setTab(string $tabName): void
    {
        $this->tab = $tabName;
    }
}; ?>

<div class="space-y-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Configuration</h1>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Gérez les paramètres de votre boutique et de votre compte.</p>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="flex flex-wrap gap-2 p-1 bg-zinc-100 dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 w-fit">
        <button wire:click="setTab('boutique')"
            class="px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ $tab === 'boutique' ? 'bg-white dark:bg-zinc-800 text-brand-primary shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">
            Boutique
        </button>
        <button wire:click="setTab('logistique')"
            class="px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ $tab === 'logistique' ? 'bg-white dark:bg-zinc-800 text-brand-primary shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">
            Logistique
        </button>
        <button wire:click="setTab('profil')"
            class="px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ $tab === 'profil' ? 'bg-white dark:bg-zinc-800 text-brand-primary shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">
            Profil
        </button>
        <button wire:click="setTab('mot-de-passe')"
            class="px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ $tab === 'mot-de-passe' ? 'bg-white dark:bg-zinc-800 text-brand-primary shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">
            Mot de passe
        </button>
        <button wire:click="setTab('apparence')"
            class="px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ $tab === 'apparence' ? 'bg-white dark:bg-zinc-800 text-brand-primary shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">
            Apparence
        </button>
        <button wire:click="setTab('securite-2fa')"
            class="px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ $tab === 'securite-2fa' ? 'bg-white dark:bg-zinc-800 text-brand-primary shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">
            Sécurité
        </button>
    </div>

    {{-- Tab Content --}}
    <div>
        @switch($tab)
            @case('boutique')
                <x-admin.settings.tabs.boutique />
                @break
            @case('logistique')
                <x-admin.settings.tabs.logistique />
                @break
            @case('profil')
                <x-admin.settings.tabs.profil />
                @break
            @case('mot-de-passe')
                <x-admin.settings.tabs.mot-de-passe />
                @break
            @case('apparence')
                <x-admin.settings.tabs.apparence />
                @break
            @case('securite-2fa')
                <x-admin.settings.tabs.securite-2fa />
                @break
            @default
                <x-admin.settings.tabs.boutique />
        @endswitch
    </div>
</div>
