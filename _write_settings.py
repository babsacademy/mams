# -*- coding: utf-8 -*-
import os

content = r'''<?php
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Paramètres')] #[Layout('layouts.app')] class extends Component
{
    public string $tab = 'general';

    // Général
    public string $siteName = '';
    public string $siteTagline = '';

    // Contact
    public string $whatsappNumber = '';

    // Livraison
    public string $freeShippingThreshold = '';

    /** @var array<int, array{value: string, label: string, price: int|string}> */
    public array $zones = [];

    // Nouvelle zone en cours d'ajout
    public string $newZoneValue = '';
    public string $newZoneLabel = '';
    public string $newZonePrice = '';

    public string $successMessage = '';

    public function mount(): void
    {
        $this->siteName              = Setting::get('site_name', 'Sacoche Chic');
        $this->siteTagline           = Setting::get('site_tagline', '');
        $this->whatsappNumber        = Setting::get('whatsapp_number', '');
        $this->freeShippingThreshold = Setting::get('free_shipping_threshold', '75000');
        $this->zones                 = Setting::shippingZones();
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'siteName'              => ['required', 'string', 'max:100'],
            'siteTagline'           => ['nullable', 'string', 'max:200'],
            'whatsappNumber'        => ['required', 'string', 'max:20'],
            'freeShippingThreshold' => ['required', 'integer', 'min:0'],
        ]);

        Setting::set('site_name', $this->siteName, 'general');
        Setting::set('site_tagline', $this->siteTagline, 'general');
        Setting::set('whatsapp_number', $this->whatsappNumber, 'contact');
        Setting::set('free_shipping_threshold', $this->freeShippingThreshold, 'livraison');

        $this->successMessage = 'Paramètres généraux enregistrés.';
    }

    public function saveZones(): void
    {
        Setting::set('shipping_zones', json_encode(array_values($this->zones)), 'livraison');
        $this->successMessage = 'Zones de livraison enregistrées.';
    }

    public function addZone(): void
    {
        $this->validate([
            'newZoneValue' => ['required', 'string', 'max:50'],
            'newZoneLabel' => ['required', 'string', 'max:100'],
            'newZonePrice' => ['required', 'integer', 'min:0'],
        ]);

        $this->zones[] = [
            'value' => $this->newZoneValue,
            'label' => $this->newZoneLabel,
            'price' => (int) $this->newZonePrice,
        ];

        $this->newZoneValue = '';
        $this->newZoneLabel = '';
        $this->newZonePrice = '';
    }

    public function removeZone(int $index): void
    {
        unset($this->zones[$index]);
        $this->zones = array_values($this->zones);
    }

    public function updatedSuccessMessage(): void
    {
        // reset after 3s via JS
    }
}; ?>

<div class="space-y-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Configuration</h1>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Gérez les paramètres globaux de votre boutique.</p>
        </div>
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

    {{-- Tabs Modernes --}}
    <div class="flex p-1 bg-zinc-100 dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 w-fit">
        <button wire:click="$set('tab', 'general')"
            class="px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ $tab === 'general' ? 'bg-white dark:bg-zinc-800 text-brand-pink shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">
            Boutique
        </button>
        <button wire:click="$set('tab', 'livraison')"
            class="px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ $tab === 'livraison' ? 'bg-white dark:bg-zinc-800 text-brand-pink shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">
            Logistique
        </button>
    </div>

    {{-- Tab Général --}}
    @if($tab === 'general')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start animate-in fade-in slide-in-from-bottom-2 duration-500">
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-10">
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="size-9 bg-zinc-50 dark:bg-zinc-800 rounded-xl flex items-center justify-center">
                            <flux:icon.building-storefront class="size-5 text-zinc-400" />
                        </div>
                        <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Identité</h3>
                    </div>

                    <div class="grid gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Nom de l'enseigne</label>
                            <flux:input wire:model="siteName" variant="filled" placeholder="Ex: Sacoche Chic" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Slogan publicitaire</label>
                            <flux:input wire:model="siteTagline" variant="filled" placeholder="Ex: L'Élégance Maroquinerie" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                        </div>
                    </div>
                </div>

                <div class="space-y-6 border-t border-zinc-100 dark:border-zinc-800/50 pt-10">
                    <div class="flex items-center gap-3">
                        <div class="size-9 bg-zinc-50 dark:bg-zinc-800 rounded-xl flex items-center justify-center">
                            <flux:icon.truck class="size-5 text-zinc-400" />
                        </div>
                        <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Flux & Ventes</h3>
                    </div>

                    <div class="grid gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">WhatsApp Business</label>
                            <flux:input wire:model="whatsappNumber" variant="filled" placeholder="Ex: 221770000000" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                            <p class="text-[9px] text-zinc-500 font-bold uppercase tracking-wider">Format: Code pays + numéro (ex: 221...)</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Franchise de port (FCFA)</label>
                            <flux:input wire:model="freeShippingThreshold" type="number" variant="filled" min="0" placeholder="Ex: 75000" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                            <p class="text-[9px] text-zinc-500 font-bold uppercase tracking-wider">Montant min. pour livraison offerte</p>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <flux:button wire:click="saveGeneral" variant="primary" class="!bg-brand-pink border-none font-black uppercase tracking-widest text-[10px] py-4 w-full rounded-2xl shadow-xl shadow-brand-pink/20">
                        Appliquer les modifications
                    </flux:button>
                </div>
            </div>

            <div class="bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-10 hidden lg:block">
                <div class="size-14 bg-white dark:bg-zinc-800 rounded-2xl flex items-center justify-center mb-8 shadow-sm">
                    <flux:icon.information-circle class="size-7 text-zinc-400" />
                </div>
                <h4 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight mb-4">Conseils</h4>
                <ul class="space-y-6">
                    <li class="flex gap-4">
                        <span class="size-6 rounded-full bg-brand-pink/10 text-brand-pink flex items-center justify-center text-[10px] font-black shrink-0">1</span>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed font-medium">Le <span class="text-zinc-900 dark:text-white font-bold">nom du site</span> est utilisé pour les notifications par mail et les titres SEO.</p>
                    </li>
                    <li class="flex gap-4">
                        <span class="size-6 rounded-full bg-brand-pink/10 text-brand-pink flex items-center justify-center text-[10px] font-black shrink-0">2</span>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed font-medium">Assurez-vous que le numéro <span class="text-zinc-900 dark:text-white font-bold">WhatsApp</span> est valide pour permettre aux clients de vous contacter d'un clic.</p>
                    </li>
                </ul>
            </div>
        </div>
    @endif

    {{-- Tab Zones de livraison --}}
    @if($tab === 'livraison')
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-10 items-start animate-in fade-in slide-in-from-bottom-2 duration-500">
            <div class="xl:col-span-2 space-y-8">
                {{-- Liste des zones --}}
                <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                    <div class="px-10 py-8 border-b border-zinc-100 dark:border-zinc-800/50 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-black text-zinc-900 dark:text-white uppercase tracking-tight">Tarification par secteur</h3>
                            <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest mt-1">Définit les frais de port appliqués au panier.</p>
                        </div>
                        <flux:button wire:click="saveZones" variant="primary" class="!bg-brand-pink border-none font-black uppercase tracking-widest text-[9px] px-6 rounded-xl shadow-lg shadow-brand-pink/10">
                            Enregistrer
                        </flux:button>
                    </div>

                    @if(empty($zones))
                        <div class="px-10 py-24 text-center">
                            <div class="size-14 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <flux:icon.truck class="size-7 text-zinc-200 dark:text-zinc-700" />
                            </div>
                            <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Grille tarifaire vierge</p>
                            <p class="text-xs text-zinc-400 mt-2">Commencez par ajouter une zone de livraison.</p>
                        </div>
                    @else
                        <div class="divide-y divide-zinc-50 dark:divide-zinc-800/30">
                            @foreach($zones as $i => $zone)
                                <div class="group flex items-center gap-8 px-10 py-5 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors" wire:key="zone-{{ $i }}">
                                    <div class="size-10 bg-zinc-100 dark:bg-zinc-800 rounded-xl flex items-center justify-center shrink-0">
                                        <flux:icon.map-pin class="size-5 text-zinc-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base font-black text-zinc-900 dark:text-white tracking-tight">{{ $zone['label'] }}</p>
                                        <p class="text-[10px] text-zinc-400 font-black uppercase tracking-widest mt-1">{{ $zone['value'] }}</p>
                                    </div>
                                    <div class="flex items-center gap-6">
                                        <div class="w-48">
                                            <flux:input
                                                wire:model="zones.{{ $i }}.price"
                                                type="number" min="0"
                                                variant="filled"
                                                class="!bg-zinc-100 dark:!bg-zinc-800 text-right font-black !h-11"
                                                suffix="FCFA"
                                            />
                                        </div>
                                        <button wire:click="removeZone({{ $i }})"
                                            wire:confirm="Supprimer cette zone ?"
                                            class="size-10 flex items-center justify-center text-zinc-300 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all opacity-0 group-hover:opacity-100">
                                            <flux:icon.trash class="size-5" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-8">
                {{-- Ajouter une zone --}}
                <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-8">
                    <div class="flex items-center gap-3">
                        <div class="size-9 bg-zinc-50 dark:bg-zinc-800 rounded-xl flex items-center justify-center">
                            <flux:icon.plus class="size-5 text-zinc-400" />
                        </div>
                        <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Ajout Rapide</h3>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Identifiant interne (Sans espaces)</label>
                            <flux:input wire:model="newZoneValue" variant="filled" placeholder="ex: dakar_centre" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Nom de la zone (Public)</label>
                            <flux:input wire:model="newZoneLabel" variant="filled" placeholder="ex: Plateau, Hann..." class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Tarif standard (FCFA)</label>
                            <flux:input wire:model="newZonePrice" type="number" variant="filled" min="0" placeholder="ex: 2000" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                        </div>
                    </div>

                    <div class="pt-4">
                        <flux:button wire:click="addZone" variant="primary" class="!bg-brand-pink border-none font-black uppercase tracking-widest text-[10px] py-4 w-full rounded-2xl shadow-xl shadow-brand-pink/20">
                            Ajouter au catalogue
                        </flux:button>
                    </div>
                </div>

                <div class="p-8 bg-zinc-50 dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800">
                    <p class="text-[10px] text-zinc-400 font-black uppercase tracking-widest mb-2">Note Importante</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">Assurez-vous d'avoir configuré une zone "Hors zone" ou par défaut si vous livrez dans des régions non listées.</p>
                </div>
            </div>
        </div>
    @endif
</div>
'''

target_path = os.path.join(
    r'c:\dev\schic\resources\views\components\admin\settings',
    '\u26a1index.blade.php'
)

with open(target_path, 'w', encoding='utf-8') as f:
    f.write(content)

print(f'Written to: {target_path}')
print(f'Size: {os.path.getsize(target_path)} bytes')
