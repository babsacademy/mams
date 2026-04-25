<?php

use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Produits')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterStatus = '';

    public ?int $confirmDeleteId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $productId): void
    {
        Gate::authorize('admin-action');

        $product = Product::findOrFail($productId);
        $product->update(['is_active' => ! $product->is_active]);
        $this->dispatch('notify', message: 'Statut mis à jour.');
    }

    public function confirmDelete(int $productId): void
    {
        $this->confirmDeleteId = $productId;
    }

    public function deleteProduct(): void
    {
        Gate::authorize('admin-action');

        if ($this->confirmDeleteId) {
            Product::findOrFail($this->confirmDeleteId)->delete();
            $this->confirmDeleteId = null;
            $this->dispatch('notify', message: 'Produit supprimé.');
        }
    }

    #[Computed]
    public function products(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Product::with('category')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->filterStatus === 'low_stock', fn ($q) => $q->where('stock', '<=', 5))
            ->latest()
            ->paginate(15);
    }
}; ?>

<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Inventaire</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Gérez vos produits, stocks et visibilité.</p>
        </div>

        <flux:button href="{{ route('admin.products.create') }}" variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px] py-3 shadow-lg shadow-brand-primary/20">
            <flux:icon.plus class="size-4 mr-2" />
            Nouveau Produit
        </flux:button>
    </div>

    {{-- Filtres --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-8">
        <div class="flex-1 relative group">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Chercher une référence, un nom…" icon="magnifying-glass" variant="filled" class="!bg-white dark:!bg-zinc-900 border-zinc-200 dark:border-zinc-800 !h-11 font-bold" />
        </div>
        <flux:select wire:model.live="filterStatus" class="sm:w-64 !h-11">
            <flux:select.option value="">Tous les statuts</flux:select.option>
            <flux:select.option value="active">Produits Actifs</flux:select.option>
            <flux:select.option value="inactive">Produits Masqués</flux:select.option>
            <flux:select.option value="low_stock">Stock Critique (≤5)</flux:select.option>
        </flux:select>
    </div>

    {{-- Vue cartes mobile --}}
    <div class="sm:hidden space-y-3 mb-6">
        @forelse($this->products as $product)
            @php
                $productImageUrl = $product->image_url
                    ? (Str::startsWith($product->image_url, ['http://', 'https://', 'data:', '/'])
                        ? $product->image_url
                        : asset('storage/'.ltrim($product->image_url, '/')))
                    : null;
                $stockColor = $product->stock === 0 ? 'rose' : ($product->stock <= 5 ? 'amber' : 'emerald');
                $stockBg = ['rose' => 'bg-rose-500/10 text-rose-400 border-rose-500/20', 'amber' => 'bg-amber-500/10 text-amber-400 border-amber-500/20', 'emerald' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'][$stockColor];
            @endphp
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden shadow-lg">
                <div class="flex items-center gap-4 p-4">
                    <div class="relative size-16 rounded-xl overflow-hidden border border-zinc-700 bg-zinc-800 shrink-0">
                        @if($productImageUrl)
                            <img src="{{ $productImageUrl }}" alt="{{ $product->name }}" class="absolute inset-0 w-full h-full object-cover">
                        @else
                            <div class="size-full flex items-center justify-center">
                                <flux:icon.photo class="size-5 text-zinc-600" />
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-white uppercase tracking-tight truncate">{{ $product->name }}</p>
                        <p class="text-[9px] font-black text-zinc-500 uppercase tracking-widest mt-0.5">ID-{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}</p>
                        <p class="font-black text-brand-primary text-lg mt-1">{{ number_format($product->price, 0, ',', ' ') }} <span class="text-[10px] text-zinc-500 font-bold">FCFA</span></p>
                    </div>
                    <flux:switch wire:click="toggleActive({{ $product->id }})" :checked="$product->is_active" size="sm" color="pink" />
                </div>
                <div class="flex items-center justify-between px-4 py-3 border-t border-zinc-800">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 {{ $stockBg }} text-[9px] font-black uppercase tracking-widest rounded-full border">
                        <span class="size-1.5 rounded-full bg-current"></span>
                        {{ $product->stock }} Unités
                    </span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="size-9 flex items-center justify-center rounded-xl bg-zinc-800 text-zinc-400 hover:text-brand-primary hover:bg-brand-primary/10 transition-colors">
                            <flux:icon.pencil-square class="size-4" />
                        </a>
                        <button wire:click="confirmDelete({{ $product->id }})" class="size-9 flex items-center justify-center rounded-xl bg-zinc-800 text-zinc-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors">
                            <flux:icon.trash class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-20 text-zinc-500">
                <flux:icon.archive-box class="size-10 mx-auto mb-4 text-zinc-700" />
                <p class="font-black uppercase tracking-widest text-sm">Aucun produit</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination mobile --}}
    @if($this->products->hasPages())
        <div class="sm:hidden px-2 py-4">
            {{ $this->products->links() }}
        </div>
    @endif

    {{-- Table desktop --}}
    <div class="hidden sm:block bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-800/50">
                        <th class="text-left pl-6 pr-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.15em] whitespace-nowrap">Article</th>
                        <th class="text-left px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.15em] hidden md:table-cell whitespace-nowrap">Classification</th>
                        <th class="text-left px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.15em] whitespace-nowrap">Tarif</th>
                        <th class="text-center px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.15em] hidden sm:table-cell whitespace-nowrap">Stock</th>
                        <th class="text-center px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.15em] whitespace-nowrap">Visibilité</th>
                        <th class="text-right px-6 py-5 sticky right-0 bg-white dark:bg-zinc-900"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/30">
                    @forelse($this->products as $product)
                        <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-all duration-300">
                            <td class="px-6 py-5 whitespace-nowrap max-w-xs">
                                <div class="flex items-center gap-4">
                                    <div class="relative size-16 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 shrink-0">
                                        @if($product->image_url)
                                            @php
                                                $productImageUrl = Str::startsWith($product->image_url, ['http://', 'https://', 'data:', '/'])
                                                    ? $product->image_url
                                                    : asset('storage/'.ltrim($product->image_url, '/'));
                                            @endphp
                                            <img src="{{ $productImageUrl }}" alt="{{ $product->name }}"
                                                 loading="lazy"
                                                 class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                        @else
                                            <div class="size-full flex items-center justify-center">
                                               <flux:icon.photo class="size-5 text-zinc-300 opacity-50" />
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-black text-zinc-900 dark:text-zinc-100 leading-tight text-base group-hover:text-brand-primary transition-colors truncate">{{ $product->name }}</p>
                                        <div class="flex items-center gap-2 mt-1.5">
                                            <span class="text-[9px] font-black text-zinc-400 uppercase tracking-widest bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">ID-{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}</span>
                                            @if($product->badge)
                                                <span class="px-2 py-0.5 bg-brand-primary/10 text-[9px] uppercase font-black text-brand-primary rounded tracking-wider border border-brand-primary/20">{{ $product->badge }}</span>
                                            @endif
                                        </div>
                                        @if($product->length_label || $product->color_label)
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @if($product->length_label)
                                                    <span class="rounded-full border border-zinc-200 px-2 py-1 text-[9px] font-bold uppercase tracking-[0.14em] text-zinc-500 dark:border-zinc-700 dark:text-zinc-300">
                                                        Longueur {{ $product->length_label }}
                                                    </span>
                                                @endif
                                                @if($product->color_label)
                                                    <span class="rounded-full border border-zinc-200 px-2 py-1 text-[9px] font-bold uppercase tracking-[0.14em] text-zinc-500 dark:border-zinc-700 dark:text-zinc-300">
                                                        {{ $product->color_label }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 hidden md:table-cell whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1.5 bg-zinc-100 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400 text-[10px] font-black uppercase tracking-widest rounded-full border border-zinc-200/50 dark:border-zinc-700/50">
                                    {{ $product->category?->name ?? 'Indéfini' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-black text-zinc-900 dark:text-zinc-100 text-lg">
                                        {{ number_format($product->price, 0, ',', ' ') }} <span class="text-[10px] font-bold text-zinc-400">FCFA</span>
                                    </span>
                                    @if($product->original_price)
                                        <span class="text-[10px] text-zinc-400 line-through font-bold decoration-rose-500/30">
                                            {{ number_format($product->original_price, 0, ',', ' ') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center hidden sm:table-cell whitespace-nowrap">
                                @php
                                    $stockColor = $product->stock === 0 ? 'rose' : ($product->stock <= 5 ? 'amber' : 'emerald');
                                    $stockBg = [
                                        'rose' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 border-rose-200 dark:border-rose-500/20',
                                        'amber' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 border-amber-200 dark:border-amber-500/20',
                                        'emerald' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:border-emerald-500/20',
                                    ][$stockColor];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $stockBg }} text-[10px] font-black uppercase tracking-widest rounded-2xl border">
                                    <span class="size-1.5 rounded-full bg-current"></span>
                                    {{ $product->stock }} <span class="opacity-70">Unités</span>
                                </span>
                            </td>
                            <td class="px-6 py-5 text-center whitespace-nowrap">
                                <div class="flex justify-center">
                                    <flux:switch wire:click="toggleActive({{ $product->id }})" :checked="$product->is_active" size="sm" color="pink" />
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-zinc-900 group-hover:bg-zinc-50/50 dark:group-hover:bg-zinc-800/30 transition-colors duration-300">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button href="{{ route('admin.products.edit', $product) }}" size="sm" variant="ghost" icon="pencil-square" inset class="text-zinc-400 hover:text-brand-primary" />
                                    <flux:button wire:click="confirmDelete({{ $product->id }})" size="sm" variant="ghost" icon="trash" inset class="text-zinc-400 hover:text-rose-500" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-32 text-center">
                                <div class="size-16 bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                                    <flux:icon.archive-box class="size-8 text-zinc-200 dark:text-zinc-700" />
                                </div>
                                <h3 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Aucun produit en stock</h3>
                                <p class="text-sm text-zinc-400 mt-2 max-w-xs mx-auto">Votre inventaire est vide. Ajoutez vos premières créations pour commencer à vendre.</p>
                                <flux:button href="{{ route('admin.products.create') }}" variant="filled" class="mt-8 !bg-zinc-900 dark:!bg-white dark:!text-zinc-900 font-black uppercase tracking-widest text-[9px]">Créer le premier produit</flux:button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->products->hasPages())
            <div class="px-6 py-5 border-t border-zinc-100 dark:border-zinc-800/50 bg-zinc-50/10">
                {{ $this->products->links() }}
            </div>
        @endif
    </div>

    {{-- Modal suppression --}}
    @if($confirmDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-6" x-data x-on:keydown.escape.window="$wire.set('confirmDeleteId', null)">
            <div class="absolute inset-0 bg-zinc-950/60 backdrop-blur-sm transition-opacity" wire:click="$set('confirmDeleteId', null)"></div>
            <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl p-10 w-full max-w-md border border-zinc-200 dark:border-zinc-800 text-center animate-in zoom-in-95 duration-200">
                <div class="size-16 bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <flux:icon.trash class="size-8 text-rose-500" />
                </div>
                <h2 class="text-2xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Supprimer ?</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-3">Cette action est <span class="font-black text-rose-500 uppercase tracking-widest text-[10px]">irréversible</span>. Vous perdrez toutes les données liées à ce produit.</p>
                <div class="grid grid-cols-2 gap-4 mt-10">
                    <flux:button wire:click="$set('confirmDeleteId', null)" variant="ghost" class="!h-11 font-black uppercase tracking-widest text-[10px]">Annuler</flux:button>
                    <flux:button wire:click="deleteProduct" variant="danger" class="!h-11 !bg-rose-600 border-none font-black uppercase tracking-widest text-[10px] shadow-lg shadow-rose-600/20">Confirmer</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
