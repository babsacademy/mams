<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component {
    /** @var array<int, array{value: string, label: string, price: int|string}> */
    public array $zones = [];

    public string $newZoneValue = '';
    public string $newZoneLabel = '';
    public string $newZonePrice = '';

    public string $successMessage = '';

    public function mount(): void
    {
        $this->zones = Setting::shippingZones();
    }

    public function saveZones(): void
    {
        Gate::authorize('admin-action');

        Setting::set('shipping_zones', json_encode(array_values($this->zones)), 'livraison');
        $this->successMessage = 'Zones de livraison enregistrées.';
    }

    public function addZone(): void
    {
        Gate::authorize('admin-action');

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
        Gate::authorize('admin-action');

        unset($this->zones[$index]);
        $this->zones = array_values($this->zones);
    }
}; ?>

<div class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500">
    @if($successMessage ?? false)
        <div class="fixed top-8 right-8 z-[60] flex items-center gap-3 rounded-2xl bg-emerald-500 text-white px-6 py-4 shadow-2xl shadow-emerald-500/20 animate-in slide-in-from-right-10 duration-500"
             x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <div class="size-6 bg-white/20 rounded-full flex items-center justify-center">
                <flux:icon.check class="size-4" />
            </div>
            <p class="text-sm font-black uppercase tracking-widest">{{ $successMessage }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-10 items-start">
        <div class="xl:col-span-2 space-y-8">
            {{-- Liste des zones --}}
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                <div class="px-10 py-8 border-b border-zinc-100 dark:border-zinc-800/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-zinc-900 dark:text-white uppercase tracking-tight">Tarification par secteur</h3>
                        <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest mt-1">Définit les frais de port appliqués au panier.</p>
                    </div>
                    <flux:button wire:click="saveZones" variant="primary" class="!bg-emerald-500 border-none font-black uppercase tracking-widest text-[9px] px-6 rounded-xl shadow-lg shadow-emerald-500/10">
                        Enregistrer
                    </flux:button>
                </div>

                @if(empty($zones))
                    <div class="px-10 py-24 text-center">
                        <div class="size-16 bg-zinc-50 dark:bg-zinc-800 rounded-3xl flex items-center justify-center mx-auto mb-6">
                            <flux:icon.truck class="size-8 text-zinc-200 dark:text-zinc-700" />
                        </div>
                        <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Grille tarifaire vierge</p>
                        <p class="text-xs text-zinc-400 mt-2">Commencez par ajouter une zone de livraison.</p>
                    </div>
                @else
                    <div class="divide-y divide-zinc-50 dark:divide-zinc-800/30">
                        @foreach($zones as $i => $zone)
                            <div class="group flex items-center gap-8 px-10 py-6 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors" wire:key="zone-{{ $i }}">
                                <div class="size-12 bg-zinc-100 dark:bg-zinc-800 rounded-2xl flex items-center justify-center shrink-0">
                                    <flux:icon.map-pin class="size-6 text-zinc-400" />
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
                                            class="!bg-zinc-100 dark:!bg-zinc-800 text-right font-black !h-10"
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
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-8">
                <div class="flex items-center gap-3">
                    <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                        <flux:icon.plus class="size-5 text-zinc-400" />
                    </div>
                    <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Ajout Rapide</h3>
                </div>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Identifiant interne (Sans espaces)</label>
                        <flux:input wire:model="newZoneValue" variant="filled" placeholder="ex: dakar_centre" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Nom de la zone (Public)</label>
                        <flux:input wire:model="newZoneLabel" variant="filled" placeholder="ex: Plateau, Hann..." class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Tarif standard (FCFA)</label>
                        <flux:input wire:model="newZonePrice" type="number" variant="filled" min="0" placeholder="ex: 2000" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                    </div>
                </div>

                <div class="pt-4">
                    <flux:button wire:click="addZone" variant="primary" class="!bg-zinc-900 border-none dark:!bg-white dark:!text-zinc-900 font-black uppercase tracking-widest text-[10px] py-4 w-full rounded-2xl shadow-xl shadow-zinc-900/10">
                        Ajouter au catalogue
                    </flux:button>
                </div>
            </div>

            <div class="p-8 bg-zinc-50 dark:bg-zinc-900 rounded-[2rem] border border-zinc-200 dark:border-zinc-800">
                <p class="text-[10px] text-zinc-400 font-black uppercase tracking-widest mb-2">Note Importante</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">Assurez-vous d'avoir configuré une zone "Hors zone" ou par défaut si vous livrez dans des régions non listées.</p>
            </div>
        </div>
    </div>
</div>
