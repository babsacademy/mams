<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component {
    public string $color_primary = '';
    public string $color_primary_hover = '';

    public string $successMessage = '';

    public function mount(): void
    {
        $this->color_primary = Setting::get('color_primary', '#6366f1');
        $this->color_primary_hover = Setting::get('color_primary_hover', '#4f46e5');
    }

    public function saveAppearance(): void
    {
        Gate::authorize('admin-action');

        $this->validate([
            'color_primary' => ['required', 'regex:/^#[0-9A-F]{6}$/i'],
            'color_primary_hover' => ['required', 'regex:/^#[0-9A-F]{6}$/i'],
        ]);

        Setting::set('color_primary', $this->color_primary, 'appearance');
        Setting::set('color_primary_hover', $this->color_primary_hover, 'appearance');

        $this->successMessage = 'Apparence mise à jour avec succès.';
    }

    public function resetToDefaults(): void
    {
        Gate::authorize('admin-action');

        $this->color_primary = '#6366f1';
        $this->color_primary_hover = '#4f46e5';
        $this->saveAppearance();
        $this->successMessage = 'Couleurs réinitialisées aux valeurs par défaut.';
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-10">
            <div class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                        <flux:icon.swatch class="size-5 text-zinc-400" />
                    </div>
                    <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Couleurs du Dashboard</h3>
                </div>

                <div class="grid gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Couleur primaire</label>
                        <div class="flex gap-3">
                            <input
                                type="color"
                                wire:model.live="color_primary"
                                class="w-16 h-12 rounded-xl cursor-pointer border-2 border-zinc-200 dark:border-zinc-700"
                            />
                            <flux:input
                                wire:model.live="color_primary"
                                variant="filled"
                                placeholder="#6366f1"
                                class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold flex-1"
                            />
                        </div>
                        <p class="text-[9px] text-zinc-500 font-bold uppercase tracking-wider">Utilisée pour les boutons, liens et éléments interactifs</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Couleur au survol (hover)</label>
                        <div class="flex gap-3">
                            <input
                                type="color"
                                wire:model.live="color_primary_hover"
                                class="w-16 h-12 rounded-xl cursor-pointer border-2 border-zinc-200 dark:border-zinc-700"
                            />
                            <flux:input
                                wire:model.live="color_primary_hover"
                                variant="filled"
                                placeholder="#4f46e5"
                                class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold flex-1"
                            />
                        </div>
                        <p class="text-[9px] text-zinc-500 font-bold uppercase tracking-wider">Appliquée au survol de la souris</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-4">
                <flux:button wire:click="saveAppearance" variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px] py-4 rounded-2xl shadow-xl shadow-brand-primary/20">
                    Enregistrer les modifications
                </flux:button>
                <flux:button wire:click="resetToDefaults" variant="ghost" class="font-black uppercase tracking-widest text-[10px] py-3 rounded-2xl text-zinc-600 dark:text-zinc-400">
                    Réinitialiser aux valeurs par défaut
                </flux:button>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-8">
            <div class="flex items-center gap-3">
                <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                    <flux:icon.eye class="size-5 text-zinc-400" />
                </div>
                <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Aperçu</h3>
            </div>

            <div class="space-y-4">
                <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Couleur primaire</p>
                <div class="w-full h-24 rounded-2xl border-2 border-zinc-200 dark:border-zinc-700 transition-colors" :style="`background-color: ${$wire.color_primary}`"></div>

                <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mt-6">Couleur au survol</p>
                <div class="w-full h-24 rounded-2xl border-2 border-zinc-200 dark:border-zinc-700 transition-colors" :style="`background-color: ${$wire.color_primary_hover}`"></div>
            </div>

            <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800">
                <p class="text-[10px] font-black text-zinc-600 dark:text-zinc-300 uppercase tracking-widest mb-2">Info</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">Les changements de couleur sont appliqués en temps réel sur tout le dashboard. Rafraîchissez la page pour voir les changements sur tous les éléments.</p>
            </div>
        </div>
    </div>
</div>
