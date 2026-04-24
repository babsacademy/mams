#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Write the improved create-edit blade file with Unicode filename."""

import os

filepath = os.path.join(
    "c:", os.sep, "dev", "schic", "resources", "views", "components",
    "admin", "products", "\u26a1create-edit.blade.php"
)

php_block = r"""<?php

use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Services\ImageConverter;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Produit')] #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads, WithPagination;

    public ?Product $product = null;

    public string $name          = '';
    public string $description   = '';
    public string $price         = '';
    public string $originalPrice = '';
    public string $stock         = '0';
    public string $imageUrl      = '';
    public string $badge         = '';
    public int|string $categoryId = '';
    public bool $isActive   = true;
    public bool $isFeatured = false;
    public bool $isNew      = false;

    // Médiathèque
    public bool $showMediaPicker   = false;
    public string $mediaSearch     = '';
    public bool $isUploadingMedia  = false;
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $mediaUploads     = [];
    public string $urlMode         = 'media'; // 'media' | 'external'

    public function mount(?Product $product = null): void
    {
        if ($product?->exists) {
            $this->product       = $product;
            $this->name          = $product->name;
            $this->description   = $product->description ?? '';
            $this->price         = (string) $product->price;
            $this->originalPrice = (string) ($product->original_price ?? '');
            $this->stock         = (string) $product->stock;
            $this->imageUrl      = $product->image_url ?? '';
            $this->badge         = $product->badge ?? '';
            $this->categoryId    = $product->category_id;
            $this->isActive      = $product->is_active;
            $this->isFeatured    = $product->is_featured;
            $this->isNew         = $product->is_new;
        }
    }

    public function updatedMediaSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMediaUploads(): void
    {
        $this->isUploadingMedia = true;
        $converter = new ImageConverter();

        foreach ($this->mediaUploads as $upload) {
            $tmpPath   = $upload->getRealPath();
            $webpPath  = $converter->toWebP($tmpPath);
            $dimensions = $converter->getDimensions($webpPath);

            $filename    = uniqid('media_', true) . '.webp';
            $storagePath = 'media/' . $filename;

            Storage::disk('public')->put($storagePath, file_get_contents($webpPath));
            unlink($webpPath);

            Media::create([
                'filename'      => $filename,
                'original_name' => pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME) . '.webp',
                'path'          => $storagePath,
                'disk'          => 'public',
                'size'          => Storage::disk('public')->size($storagePath),
                'width'         => $dimensions['width'],
                'height'        => $dimensions['height'],
            ]);
        }

        $this->mediaUploads     = [];
        $this->isUploadingMedia = false;
        unset($this->pickerMedia);
    }

    public function pickMedia(int $mediaId): void
    {
        $media          = Media::findOrFail($mediaId);
        $this->imageUrl = $media->url;
        $this->showMediaPicker = false;
        $this->mediaSearch     = '';
    }

    public function clearImage(): void
    {
        $this->imageUrl = '';
    }

    public function save(): void
    {
        $urlValidation = $this->urlMode === 'external'
            ? ['nullable', 'url', 'max:500']
            : ['nullable', 'string', 'max:500'];

        $validated = $this->validate([
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'price'         => ['required', 'integer', 'min:0'],
            'originalPrice' => ['nullable', 'integer', 'min:0'],
            'stock'         => ['required', 'integer', 'min:0'],
            'imageUrl'      => $urlValidation,
            'badge'         => ['nullable', 'string', 'max:50'],
            'categoryId'    => ['required', 'exists:categories,id'],
            'isActive'      => ['boolean'],
            'isFeatured'    => ['boolean'],
            'isNew'         => ['boolean'],
        ]);

        $data = [
            'name'           => $validated['name'],
            'description'    => $validated['description'],
            'price'          => (int) $validated['price'],
            'original_price' => $validated['originalPrice'] ? (int) $validated['originalPrice'] : null,
            'stock'          => (int) $validated['stock'],
            'image_url'      => $validated['imageUrl'] ?: null,
            'badge'          => $validated['badge'] ?: null,
            'category_id'    => (int) $validated['categoryId'],
            'is_active'      => $validated['isActive'],
            'is_featured'    => $validated['isFeatured'],
            'is_new'         => $validated['isNew'],
        ];

        if ($this->product?->exists) {
            $this->product->update($data);
            $this->dispatch('notify', message: 'Produit mis à jour.');
        } else {
            Product::create($data);
            $this->dispatch('notify', message: 'Produit créé.');
        }

        $this->redirect(route('admin.products.index'), navigate: true);
    }

    #[Computed]
    public function categories(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function pickerMedia(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Media::query()
            ->when($this->mediaSearch, fn ($q) => $q->where('original_name', 'like', "%{$this->mediaSearch}%"))
            ->latest()
            ->paginate(18);
    }

    public function isEditing(): bool
    {
        return $this->product?->exists === true;
    }
}; ?>"""

blade_template = """
<div>
    {{-- Page Header --}}
    <div class="mb-8 flex items-center gap-4">
        <flux:button href="{{ route('admin.products.index') }}" variant="ghost" icon="arrow-left" class="text-zinc-400 hover:text-zinc-900 dark:hover:text-white">
            Retour
        </flux:button>
        <div class="border-l border-zinc-200 dark:border-zinc-700 pl-4">
            <h1 class="text-2xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">
                {{ $this->isEditing() ? 'Modifier le produit' : 'Nouveau Produit' }}
            </h1>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-0.5">
                {{ $this->isEditing() ? \u00c9dition\u00a0: $name : 'Ajoutez une nouvelle pi\u00e8ce \u00e0 votre collection.' }}
            </p>
        </div>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

        {{-- ============================================================ --}}
        {{-- LEFT COLUMN (2/3) --}}
        {{-- ============================================================ --}}
        <div class="lg:col-span-2 space-y-6 lg:space-y-8">

            {{-- Section : Informations g\u00e9n\u00e9rales --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 lg:p-8 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-lg bg-brand-pink/10 flex items-center justify-center">
                        <flux:icon.pencil-square class="size-4 text-brand-pink" />
                    </div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">D\u00e9tails g\u00e9n\u00e9raux</h2>
                </div>

                <div class="space-y-5">
                    <flux:input wire:model="name" label="D\u00e9signation" placeholder="Ex\u00a0: Le Signature Noir" variant="filled" class="!h-11" required />

                    <flux:textarea wire:model="description" label="Description d\u00e9taill\u00e9e" rows="5" placeholder="Sp\u00e9cifications, mati\u00e8res, histoire du produit\u2026" variant="filled" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <flux:input wire:model="price" label="Prix de vente (FCFA)" type="number" min="0" variant="filled" class="!h-11" required />
                        <flux:input wire:model="originalPrice" label="Prix d'origine / barr\u00e9" type="number" min="0" variant="filled" class="!h-11" placeholder="Vide si pas de promo" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <flux:input wire:model="stock" label="Stock disponible" type="number" min="0" variant="filled" class="!h-11" required />
                        <flux:input wire:model="badge" label="Badge promotionnel" placeholder="Ex\u00a0: -20% ou LIMITED" variant="filled" class="!h-11" />
                    </div>
                </div>
            </div>

            {{-- Section : Visuel produit --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 lg:p-8 space-y-6">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-lg bg-brand-pink/10 flex items-center justify-center">
                            <flux:icon.photo class="size-4 text-brand-pink" />
                        </div>
                        <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Visuel produit</h2>
                    </div>

                    {{-- Mode switcher (pill tabs) --}}
                    <div class="flex items-center p-1 bg-zinc-100 dark:bg-zinc-800 rounded-full">
                        <button type="button"
                                wire:click="$set('urlMode', 'media')"
                                class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest transition-all rounded-full {{ $urlMode === 'media' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                            M\u00e9diath\u00e8que
                        </button>
                        <button type="button"
                                wire:click="$set('urlMode', 'external')"
                                class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest transition-all rounded-full {{ $urlMode === 'external' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                            Lien externe
                        </button>
                    </div>
                </div>

                @if($urlMode === 'media')
                    @if($imageUrl)
                        {{-- Image preview with hover overlay --}}
                        <div class="relative group rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 aspect-video bg-zinc-50 dark:bg-zinc-950">
                            <img src="{{ $imageUrl }}" alt="Preview" class="size-full object-cover">
                            <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-200 flex items-center justify-center gap-3">
                                <flux:button type="button" wire:click="$set('showMediaPicker', true)" size="sm" variant="filled" class="!bg-white !text-zinc-900 !font-bold !text-xs">
                                    Changer
                                </flux:button>
                                <flux:button type="button" wire:click="clearImage" size="sm" variant="danger" class="!font-bold !text-xs">
                                    Supprimer
                                </flux:button>
                            </div>
                        </div>
                    @else
                        {{-- Upload placeholder --}}
                        <button type="button"
                                wire:click="$set('showMediaPicker', true)"
                                class="w-full aspect-video max-h-64 border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-2xl text-center hover:border-brand-pink/50 hover:bg-brand-pink/5 transition-all duration-200 group flex flex-col items-center justify-center gap-4">
                            <div class="size-14 rounded-2xl bg-zinc-100 dark:bg-zinc-800 group-hover:bg-brand-pink/10 flex items-center justify-center transition-colors">
                                <flux:icon.cloud-arrow-up class="size-7 text-zinc-400 group-hover:text-brand-pink transition-colors" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Ajouter un visuel</p>
                                <p class="text-xs text-zinc-400">Parcourez votre m\u00e9diath\u00e8que ou importez un fichier</p>
                            </div>
                        </button>
                    @endif
                @else
                    <div class="space-y-4">
                        <flux:input wire:model="imageUrl" label="URL de l'image" placeholder="https://votre-image.com/photo.jpg" variant="filled" class="!h-11" />
                        @if($imageUrl)
                            <div class="rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 aspect-video bg-zinc-50 dark:bg-zinc-950">
                                <img src="{{ $imageUrl }}" alt="Aper\u00e7u externe" class="size-full object-cover">
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- RIGHT SIDEBAR (1/3) --}}
        {{-- ============================================================ --}}
        <div class="space-y-6 lg:space-y-8">

            {{-- Classification --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 lg:p-8 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-lg bg-brand-pink/10 flex items-center justify-center">
                        <flux:icon.tag class="size-4 text-brand-pink" />
                    </div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Classification</h2>
                </div>

                <flux:select wire:model="categoryId" label="Cat\u00e9gorie" variant="listbox" class="!h-11">
                    <flux:select.option value="">Choisir une cat\u00e9gorie\u2026</flux:select.option>
                    @foreach($this->categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Visibility toggles --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 lg:p-8 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-lg bg-brand-pink/10 flex items-center justify-center">
                        <flux:icon.eye class="size-4 text-brand-pink" />
                    </div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Mise en avant</h2>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-700/50">
                        <div class="space-y-0.5">
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">Produit actif</p>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold">Visible en boutique</p>
                        </div>
                        <flux:switch wire:model="isActive" />
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-700/50">
                        <div class="space-y-0.5">
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">Best Seller</p>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold">Page d'accueil</p>
                        </div>
                        <flux:switch wire:model="isFeatured" />
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-700/50">
                        <div class="space-y-0.5">
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">Nouveaut\u00e9</p>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold">Bandeau sp\u00e9cifique</p>
                        </div>
                        <flux:switch wire:model="isNew" />
                    </div>
                </div>
            </div>

            {{-- Submit button --}}
            <flux:button type="submit" variant="primary" class="w-full !h-12 font-black uppercase tracking-widest !bg-brand-pink border-none hover:!bg-brand-pink/90 shadow-lg hover:shadow-brand-pink/25 transition-all" icon="check">
                {{ $this->isEditing() ? 'Sauvegarder' : 'Publier le produit' }}
            </flux:button>
        </div>
    </form>

    {{-- ============================================================ --}}
    {{-- Modal M\u00e9diath\u00e8que --}}
    {{-- ============================================================ --}}
    @if($showMediaPicker)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
             x-data
             x-on:keydown.escape.window="$wire.set('showMediaPicker', false)">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-zinc-950/80 backdrop-blur-md"
                 wire:click="$set('showMediaPicker', false)"></div>

            {{-- Modal panel --}}
            <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-5xl max-h-[85vh] flex flex-col overflow-hidden border border-zinc-200 dark:border-zinc-800">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 lg:px-8 py-6 border-b border-zinc-100 dark:border-zinc-800 shrink-0">
                    <div>
                        <h2 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">M\u00e9diath\u00e8que</h2>
                        <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold mt-0.5">S\u00e9lectionnez ou importez vos visuels</p>
                    </div>
                    <button type="button" wire:click="$set('showMediaPicker', false)"
                            class="size-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 flex items-center justify-center transition-colors">
                        <flux:icon.x-mark class="size-4 text-zinc-500" />
                    </button>
                </div>

                {{-- Upload zone --}}
                <div class="px-6 lg:px-8 pt-5 shrink-0"
                     x-data="{ dragging: false }"
                     x-on:dragover.prevent="dragging = true"
                     x-on:dragleave.prevent="dragging = false"
                     x-on:drop.prevent="dragging = false; $wire.uploadMultiple('mediaUploads', Array.from($event.dataTransfer.files))">
                    <div
                        :class="dragging ? 'border-brand-pink bg-brand-pink/5' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/30 hover:bg-zinc-100 dark:hover:bg-zinc-800/50'"
                        class="border-2 border-dashed rounded-2xl px-6 py-5 text-center transition-all cursor-pointer group"
                        x-on:click="$refs.pickerInput.click()"
                    >
                        <input x-ref="pickerInput" type="file" class="hidden" multiple
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               x-on:change="$wire.uploadMultiple('mediaUploads', Array.from($refs.pickerInput.files))">

                        @if($isUploadingMedia)
                            <div class="flex items-center justify-center gap-3 py-1">
                                <div class="size-5 border-2 border-brand-pink border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-sm font-bold text-zinc-900 dark:text-white">Traitement & conversion WebP\u2026</span>
                            </div>
                        @else
                            <div class="flex items-center justify-center gap-3 py-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                <flux:icon.cloud-arrow-up class="size-5 text-brand-pink" />
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 font-bold uppercase tracking-widest">Glisser ou cliquer pour <span class="text-brand-pink">importer</span></span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Search --}}
                <div class="px-6 lg:px-8 pt-4 shrink-0">
                    <flux:input wire:model.live.debounce.300ms="mediaSearch" placeholder="Chercher dans vos fichiers\u2026" icon="magnifying-glass" variant="filled" class="!h-11" />
                </div>

                {{-- Grid --}}
                <div class="flex-1 overflow-y-auto px-6 lg:px-8 pb-6 pt-4">
                    @if($this->pickerMedia->isEmpty())
                        <div class="flex flex-col items-center justify-center py-16 text-zinc-300 dark:text-zinc-600">
                            <flux:icon.photo class="size-16 mb-3 opacity-20" />
                            <p class="text-sm font-black uppercase tracking-widest">Aucun r\u00e9sultat</p>
                            <p class="text-xs mt-1 text-zinc-400">Importez votre premi\u00e8re image pour commencer.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                            @foreach($this->pickerMedia as $media)
                                <button
                                    type="button"
                                    wire:click="pickMedia({{ $media->id }})"
                                    class="group relative aspect-square rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border-2 border-transparent hover:border-brand-pink focus:border-brand-pink focus:outline-none transition-all ring-0 focus:ring-2 focus:ring-brand-pink/30"
                                    title="{{ $media->original_name }}"
                                >
                                    <img src="{{ $media->url }}" alt="{{ $media->original_name }}"
                                         class="size-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                    <div class="absolute inset-0 bg-brand-pink/20 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                        <div class="size-8 rounded-full bg-white shadow-lg flex items-center justify-center transform scale-75 group-hover:scale-100 transition-transform duration-200">
                                            <flux:icon.check class="size-4 text-brand-pink" />
                                        </div>
                                    </div>
                                    <div class="absolute bottom-0 inset-x-0 p-1 bg-gradient-to-t from-black/50 to-transparent">
                                        <p class="text-[8px] font-bold text-white truncate text-center uppercase tracking-tight opacity-70">{{ $media->width }}\u00d7{{ $media->height }}</p>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                        @if($this->pickerMedia->hasPages())
                            <div class="pt-6 flex justify-center">{{ $this->pickerMedia->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
"""

# Fix the subtitle line that has inline PHP expression with French characters
# We need to handle the Édition line properly using Blade syntax
blade_template = blade_template.replace(
    "{{ $this->isEditing() ? \u00c9dition\u00a0: $name : 'Ajoutez une nouvelle pi\u00e8ce \u00e0 votre collection.' }}",
    "{{ $this->isEditing() ? '\u00c9dition\u00a0: ' . $name : 'Ajoutez une nouvelle pi\u00e8ce \u00e0 votre collection.' }}"
)

content = php_block + "\n" + blade_template

with open(filepath, "w", encoding="utf-8") as f:
    f.write(content)

print(f"Written {len(content)} bytes to {filepath}")
