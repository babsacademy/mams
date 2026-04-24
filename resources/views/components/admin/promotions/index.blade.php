<?php

use App\Models\Coupon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Promotions')] #[Layout('layouts.app')] class extends Component
{
    public ?int $editingId    = null;
    public string $code       = '';
    public string $type       = 'percent';
    public string $value      = '';
    public string $minOrder   = '';
    public string $maxUses    = '';
    public string $expiresAt  = '';
    public bool $isActive     = true;
    public bool $showForm     = false;
    public ?int $confirmDeleteId = null;

    public function openCreate(): void
    {
        $this->reset(['editingId', 'code', 'type', 'value', 'minOrder', 'maxUses', 'expiresAt', 'isActive', 'showForm']);
        $this->type     = 'percent';
        $this->isActive = true;
        $this->showForm = true;
    }

    public function openEdit(int $couponId): void
    {
        $coupon           = Coupon::findOrFail($couponId);
        $this->editingId  = $coupon->id;
        $this->code       = $coupon->code;
        $this->type       = $coupon->type;
        $this->value      = (string) $coupon->value;
        $this->minOrder   = $coupon->min_order !== null ? (string) $coupon->min_order : '';
        $this->maxUses    = $coupon->max_uses !== null ? (string) $coupon->max_uses : '';
        $this->expiresAt  = $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '';
        $this->isActive   = $coupon->is_active;
        $this->showForm   = true;
    }

    public function updatedCode(): void
    {
        $this->code = strtoupper($this->code);
    }

    public function save(): void
    {
        Gate::authorize('admin-action');

        $this->validate([
            'code'      => ['required', 'string', 'max:50'],
            'type'      => ['required', 'in:percent,fixed'],
            'value'     => ['required', 'numeric', 'min:0'],
            'minOrder'  => ['nullable', 'numeric', 'min:0'],
            'maxUses'   => ['nullable', 'integer', 'min:1'],
            'expiresAt' => ['nullable', 'date'],
            'isActive'  => ['boolean'],
        ]);

        $data = [
            'code'      => strtoupper($this->code),
            'type'      => $this->type,
            'value'     => (float) $this->value,
            'min_order' => $this->minOrder !== '' ? (float) $this->minOrder : null,
            'max_uses'  => $this->maxUses !== '' ? (int) $this->maxUses : null,
            'expires_at' => $this->expiresAt !== '' ? $this->expiresAt : null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            Coupon::findOrFail($this->editingId)->update($data);
            $this->dispatch('notify', message: 'Coupon mis à jour.');
        } else {
            Coupon::create($data);
            $this->dispatch('notify', message: 'Coupon créé.');
        }

        $this->showForm = false;
        $this->reset(['editingId', 'code', 'type', 'value', 'minOrder', 'maxUses', 'expiresAt', 'isActive']);
    }

    public function toggleActive(int $couponId): void
    {
        Gate::authorize('admin-action');

        $coupon = Coupon::findOrFail($couponId);
        $coupon->update(['is_active' => ! $coupon->is_active]);
    }

    public function confirmDelete(int $couponId): void
    {
        $this->confirmDeleteId = $couponId;
    }

    public function deleteCoupon(): void
    {
        Gate::authorize('admin-action');

        if ($this->confirmDeleteId) {
            Coupon::findOrFail($this->confirmDeleteId)->delete();
            $this->confirmDeleteId = null;
            $this->dispatch('notify', message: 'Coupon supprimé.');
        }
    }

    #[Computed]
    public function coupons(): \Illuminate\Database\Eloquent\Collection
    {
        return Coupon::orderByDesc('created_at')->get();
    }
}; ?>

<div>
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Promotions</h1>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Gérez vos codes promo et réductions.</p>
        </div>
        <flux:button wire:click="openCreate" variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px] py-3 shadow-lg shadow-brand-primary/20">
            <flux:icon.plus class="size-4 mr-2" />
            Nouveau coupon
        </flux:button>
    </div>

    {{-- Formulaire --}}
    @if($showForm)
        <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8 mb-8">
            <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight mb-6">
                {{ $editingId ? 'Modifier le coupon' : 'Nouveau coupon' }}
            </h2>
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="code" label="Code promo" placeholder="EX: PROMO20" required class="uppercase" />
                    <flux:select wire:model="type" label="Type">
                        <flux:select.option value="percent">Pourcentage (%)</flux:select.option>
                        <flux:select.option value="fixed">Montant fixe (FCFA)</flux:select.option>
                    </flux:select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <flux:input wire:model="value" label="{{ $type === 'percent' ? 'Valeur (%)' : 'Montant (FCFA)' }}" type="number" min="0" step="0.01" placeholder="20" required />
                    <flux:input wire:model="minOrder" label="Commande min. (FCFA)" type="number" min="0" step="0.01" placeholder="Optionnel" />
                    <flux:input wire:model="maxUses" label="Utilisations max." type="number" min="1" placeholder="Illimité" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="expiresAt" label="Date d'expiration" type="datetime-local" />
                    <div class="flex items-center gap-3 pt-6">
                        <flux:switch wire:model="isActive" />
                        <flux:label>Actif</flux:label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <flux:button type="button" wire:click="$set('showForm', false)" variant="ghost">Annuler</flux:button>
                    <flux:button type="submit" variant="primary" class="!bg-brand-primary border-none">Enregistrer</flux:button>
                </div>
            </form>
        </div>
    @endif

    {{-- Liste --}}
    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-800">
                        <th class="text-left pl-8 pr-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.1em] whitespace-nowrap">Code</th>
                        <th class="text-left px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.1em] whitespace-nowrap">Type / Valeur</th>
                        <th class="text-center px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.1em] whitespace-nowrap hidden sm:table-cell">Utilisations</th>
                        <th class="text-left px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.1em] whitespace-nowrap hidden md:table-cell">Expiration</th>
                        <th class="text-center px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.1em] whitespace-nowrap">Statut</th>
                        <th class="text-right pl-6 pr-8 py-5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                    @forelse($this->coupons as $coupon)
                        @php
                            $isExpired = $coupon->expires_at && $coupon->expires_at->isPast();
                            $isExhausted = $coupon->max_uses !== null && $coupon->uses_count >= $coupon->max_uses;
                        @endphp
                        <tr class="group hover:bg-zinc-50/80 dark:hover:bg-zinc-800/30 transition-colors duration-200">
                            <td class="pl-8 pr-6 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <span class="font-black font-mono text-zinc-900 dark:text-zinc-100 group-hover:text-brand-primary transition-colors uppercase">{{ $coupon->code }}</span>
                                    @if($isExpired)
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded-full bg-red-50 dark:bg-red-500/10 text-red-500 border border-red-200/50 dark:border-red-500/20">Expiré</span>
                                    @elseif($isExhausted)
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded-full bg-orange-50 dark:bg-orange-500/10 text-orange-500 border border-orange-200/50 dark:border-orange-500/20">Épuisé</span>
                                    @endif
                                </div>
                                @if($coupon->min_order)
                                    <p class="text-[10px] text-zinc-400 font-medium mt-0.5">Min. {{ number_format($coupon->min_order, 0, ',', ' ') }} FCFA</p>
                                @endif
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider
                                    {{ $coupon->type === 'percent' ? 'bg-purple-50 dark:bg-purple-500/10 text-purple-600 border border-purple-200/50 dark:border-purple-500/20' : 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 border border-blue-200/50 dark:border-blue-500/20' }}">
                                    @if($coupon->type === 'percent')
                                        -{{ number_format($coupon->value, 0) }}%
                                    @else
                                        -{{ number_format($coupon->value, 0, ',', ' ') }} FCFA
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-5 text-center hidden sm:table-cell whitespace-nowrap">
                                <span class="text-zinc-600 dark:text-zinc-400 font-bold">{{ $coupon->uses_count }}</span>
                                @if($coupon->max_uses !== null)
                                    <span class="text-zinc-400 text-xs"> / {{ $coupon->max_uses }}</span>
                                @else
                                    <span class="text-zinc-400 text-xs"> / ∞</span>
                                @endif
                            </td>
                            <td class="px-6 py-5 hidden md:table-cell whitespace-nowrap">
                                @if($coupon->expires_at)
                                    <span class="text-[11px] font-bold {{ $isExpired ? 'text-red-500' : 'text-zinc-600 dark:text-zinc-400' }}">
                                        {{ $coupon->expires_at->translatedFormat('d M Y, H:i') }}
                                    </span>
                                @else
                                    <span class="text-zinc-400 text-xs">Sans limite</span>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-center whitespace-nowrap">
                                <div class="flex justify-center">
                                    <flux:switch wire:click="toggleActive({{ $coupon->id }})" :checked="$coupon->is_active" size="sm" color="pink" />
                                </div>
                            </td>
                            <td class="pl-6 pr-8 py-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <flux:button wire:click="openEdit({{ $coupon->id }})" size="sm" variant="ghost" icon="pencil" inset class="text-zinc-400 hover:text-brand-primary" />
                                    <flux:button wire:click="confirmDelete({{ $coupon->id }})" size="sm" variant="ghost" icon="trash" inset class="text-zinc-400 hover:text-red-500" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-20 text-center">
                                <flux:icon.ticket class="size-10 text-zinc-200 dark:text-zinc-800 mx-auto mb-4" />
                                <p class="text-zinc-500 dark:text-zinc-400 font-medium">Aucun coupon créé.</p>
                                <p class="text-xs text-zinc-400 mt-1">Créez votre premier code promo pour booster vos ventes.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal suppression --}}
    @if($confirmDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data
             x-on:keydown.escape.window="$wire.set('confirmDeleteId', null)">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('confirmDeleteId', null)"></div>
            <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-sm p-8 border border-zinc-200 dark:border-zinc-800">
                <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight mb-2">Supprimer le coupon ?</h2>
                <p class="text-sm text-zinc-500 mb-6">Cette action est irréversible.</p>
                <div class="flex justify-end gap-3">
                    <flux:button wire:click="$set('confirmDeleteId', null)" variant="ghost">Annuler</flux:button>
                    <flux:button wire:click="deleteCoupon" variant="danger">Supprimer</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
