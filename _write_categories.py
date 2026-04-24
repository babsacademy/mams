# -*- coding: utf-8 -*-
import os

content = r'''<?php

use App\Models\Category;
use App\Models\Media;
use App\Services\ImageConverter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Cat\u00e9gories')] #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public ?int $editingId       = null;
    public string $name          = '';
    public string $imageUrl      = '';
    public bool $isActive        = true;
    public bool $showForm        = false;
    public ?int $confirmDeleteId = null;

    // M\u00e9diath\u00e8que
    public bool $showMediaPicker  = false;
    public string $mediaSearch    = '';
    public bool $isUploadingMedia = false;
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $mediaUploads = [];

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->name      = '';
        $this->imageUrl  = '';
        $this->isActive  = true;
        $this->showForm  = true;
        $this->showMediaPicker = false;
    }

    public function openEdit(int $categoryId): void
    {
        $category              = Category::findOrFail($categoryId);
        $this->editingId       = $category->id;
        $this->name            = $category->name;
        $this->imageUrl        = $category->image_url ?? '';
        $this->isActive        = $category->is_active;
        $this->showForm        = true;
        $this->showMediaPicker = false;
    }

    public function updatedMediaUploads(): void
    {
        $this->isUploadingMedia = true;
        $converter = new ImageConverter();

        foreach ($this->mediaUploads as $upload) {
            $tmpPath    = $upload->getRealPath();
            $webpPath   = $converter->toWebP($tmpPath);
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
        $media                 = Media::findOrFail($mediaId);
        $this->imageUrl        = $media->url;
        $this->showMediaPicker = false;
        $this->mediaSearch     = '';
    }

    public function save(): void
    {
        $this->validate([
            'name'     => ['required', 'string', 'max:255'],
            'imageUrl' => ['nullable', 'string', 'max:500'],
            'isActive' => ['boolean'],
        ]);

        $data = [
            'name'      => $this->name,
            'slug'      => Str::slug($this->name),
            'image_url' => $this->imageUrl ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
            $this->dispatch('notify', message: 'Cat\u00e9gorie mise \u00e0 jour.');
        } else {
            Category::create($data);
            $this->dispatch('notify', message: 'Cat\u00e9gorie cr\u00e9\u00e9e.');
        }

        $this->showForm = false;
        $this->reset(['editingId', 'name', 'imageUrl', 'isActive']);
    }

    public function toggleActive(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $category->update(['is_active' => ! $category->is_active]);
    }

    public function confirmDelete(int $categoryId): void
    {
        $this->confirmDeleteId = $categoryId;
    }

    public function deleteCategory(): void
    {
        if ($this->confirmDeleteId) {
            Category::findOrFail($this->confirmDeleteId)->delete();
            $this->confirmDeleteId = null;
            $this->dispatch('notify', message: 'Cat\u00e9gorie supprim\u00e9e.');
        }
    }

    #[Computed]
    public function categories(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::withCount('products')->orderBy('name')->get();
    }

    #[Computed]
    public function pickerMedia(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Media::query()
            ->when($this->mediaSearch, fn ($q) => $q->where('original_name', 'like', "%{$this->mediaSearch}%"))
            ->latest()
            ->paginate(18);
    }
}; ?>

<div>
    {{-- Page header --}}
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Cat\u00e9gories</h1>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Organisez et classifiez vos produits par gammes.</p>
        </div>
        <flux:button wire:click="openCreate" variant="primary" class="!bg-brand-pink border-none font-black uppercase tracking-widest text-[10px] py-3 shadow-lg shadow-brand-pink/20">
            <flux:icon.plus class="size-4 mr-2" />
            Nouvelle cat\u00e9gorie
        </flux:button>
    </div>

    {{-- Formulaire --}}
    @if($showForm)
        <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8 mb-8">
            <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight mb-6">
                {{ $editingId ? 'Modifier la cat\u00e9gorie' : 'Nouvelle cat\u00e9gorie' }}
            </h2>
            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="name" label="Nom" placeholder="Ex : Sacs \u00e0 main" required />

                {{-- Image picker --}}
                <div>
                    <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-2">Image</p>
                    @if($imageUrl)
                        <div class="flex items-center gap-4">
                            <img src="{{ $imageUrl }}" alt="Aper\u00e7u" class="w-20 h-20 object-cover rounded-2xl border border-zinc-200 dark:border-zinc-700">
                            <div class="flex flex-col gap-2">
                                <flux:button type="button" wire:click="$set('showMediaPicker', true)" size="sm" variant="ghost" icon="photo">
                                    Changer l'image
                                </flux:button>
                                <flux:button type="button" wire:click="$set('imageUrl', '')" size="sm" variant="ghost" class="text-red-500">
                                    Retirer
                                </flux:button>
                            </div>
                        </div>
                    @else
                        <button type="button"
                                wire:click="$set('showMediaPicker', true)"
                                class="w-full border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-2xl p-6 text-center hover:border-pink-400 hover:bg-pink-50 dark:hover:bg-pink-500/5 transition group">
                            <div class="flex items-center justify-center gap-2 pointer-events-none">
                                <flux:icon.photo class="size-5 text-zinc-400 group-hover:text-pink-500 transition" />
                                <span class="text-sm font-semibold text-zinc-500 group-hover:text-pink-600 transition">Choisir depuis la m\u00e9diath\u00e8que</span>
                            </div>
                        </button>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center gap-3">
                        <flux:switch wire:model="isActive" />
                        <flux:label>Active</flux:label>
                    </div>
                    <div class="flex gap-3">
                        <flux:button type="button" wire:click="$set('showForm', false)" variant="ghost">Annuler</flux:button>
                        <flux:button type="submit" variant="primary">Enregistrer</flux:button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    {{-- Category grid --}}
    @if($this->categories->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($this->categories as $category)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden group transition-all duration-300 hover:shadow-lg hover:shadow-zinc-200/50 dark:hover:shadow-zinc-900/50">
                    {{-- Image --}}
                    <div class="aspect-video overflow-hidden rounded-2xl m-3 bg-zinc-100 dark:bg-zinc-800 relative">
                        @if($category->image_url)
                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}"
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <flux:icon.photo class="size-10 text-zinc-300 dark:text-zinc-600" />
                            </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="px-5 pb-5 pt-2">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-black text-zinc-900 dark:text-zinc-100 text-base truncate group-hover:text-brand-pink transition-colors">{{ $category->name }}</h3>
                                <p class="text-[10px] text-zinc-400 uppercase tracking-widest mt-0.5">Slug: {{ $category->slug }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-[10px] font-bold uppercase tracking-widest rounded-full border border-zinc-200/50 dark:border-zinc-700/50 shrink-0">
                                <flux:icon.tag class="size-3 opacity-50" />
                                {{ $category->products_count }} {{ Str::plural('produit', $category->products_count) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <flux:switch wire:click="toggleActive({{ $category->id }})" :checked="$category->is_active" size="sm" color="pink" />
                                <span class="text-[10px] font-bold uppercase tracking-widest {{ $category->is_active ? 'text-emerald-500' : 'text-zinc-400' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:button wire:click="openEdit({{ $category->id }})" size="sm" variant="ghost" icon="pencil" inset class="text-zinc-400 hover:text-brand-pink" />
                                <flux:button wire:click="confirmDelete({{ $category->id }})" size="sm" variant="ghost" icon="trash" inset class="text-zinc-400 hover:text-rose-500" />
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty state --}}
        <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm px-10 py-32 text-center">
            <div class="size-20 bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                <flux:icon.folder-open class="size-10 text-zinc-200 dark:text-zinc-700" />
            </div>
            <h3 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Aucune cat\u00e9gorie</h3>
            <p class="text-sm text-zinc-400 mt-2 max-w-xs mx-auto">Commencez par organiser votre boutique en cr\u00e9ant votre premi\u00e8re cat\u00e9gorie.</p>
            <flux:button wire:click="openCreate" variant="filled" class="mt-8 !bg-zinc-900 dark:!bg-white dark:!text-zinc-900 font-black uppercase tracking-widest text-[9px]">Cr\u00e9er la premi\u00e8re cat\u00e9gorie</flux:button>
        </div>
    @endif

    {{-- Modal suppression --}}
    @if($confirmDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-6" x-data x-on:keydown.escape.window="$wire.set('confirmDeleteId', null)">
            <div class="absolute inset-0 bg-zinc-950/60 backdrop-blur-sm transition-opacity" wire:click="$set('confirmDeleteId', null)"></div>
            <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl p-10 w-full max-w-md border border-zinc-200 dark:border-zinc-800 text-center animate-in zoom-in-95 duration-200">
                <div class="size-20 bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <flux:icon.trash class="size-10 text-rose-500" />
                </div>
                <h2 class="text-2xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Supprimer ?</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-3">Cette action est <span class="font-black text-rose-500 uppercase tracking-widest text-[10px]">irr\u00e9versible</span>. Les produits associ\u00e9s seront aussi supprim\u00e9s.</p>
                <div class="grid grid-cols-2 gap-4 mt-10">
                    <flux:button wire:click="$set('confirmDeleteId', null)" variant="ghost" class="!h-12 font-black uppercase tracking-widest text-[10px]">Annuler</flux:button>
                    <flux:button wire:click="deleteCategory" variant="danger" class="!h-12 !bg-rose-600 border-none font-black uppercase tracking-widest text-[10px] shadow-lg shadow-rose-600/20">Confirmer</flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal m\u00e9diath\u00e8que --}}
    @if($showMediaPicker)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data
             x-on:keydown.escape.window="$wire.set('showMediaPicker', false)">
            <div class="absolute inset-0 bg-zinc-950/60 backdrop-blur-sm"
                 wire:click="$set('showMediaPicker', false)"></div>

            <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">M\u00e9diath\u00e8que</h2>
                    <button type="button" wire:click="$set('showMediaPicker', false)"
                            class="w-8 h-8 rounded-2xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 flex items-center justify-center transition">
                        <flux:icon.x-mark class="size-4 text-zinc-600 dark:text-zinc-400" />
                    </button>
                </div>

                <div class="px-6 pt-4"
                     x-data="{ dragging: false }"
                     x-on:dragover.prevent="dragging = true"
                     x-on:dragleave.prevent="dragging = false"
                     x-on:drop.prevent="dragging = false; $wire.uploadMultiple('mediaUploads', Array.from($event.dataTransfer.files))">
                    <div :class="dragging ? 'border-pink-400 bg-pink-50 dark:bg-pink-500/5' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 hover:bg-zinc-100 dark:hover:bg-zinc-800'"
                         class="border-2 border-dashed rounded-2xl px-6 py-3 text-center transition-colors cursor-pointer"
                         x-on:click="$refs.catInput.click()">
                        <input x-ref="catInput" type="file" class="hidden" multiple
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               x-on:change="$wire.uploadMultiple('mediaUploads', Array.from($refs.catInput.files))">
                        @if($isUploadingMedia)
                            <div class="flex items-center justify-center gap-2 py-1">
                                <svg class="animate-spin w-4 h-4 text-pink-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span class="text-sm text-zinc-600 dark:text-zinc-400 font-semibold">Conversion WebP\u2026</span>
                            </div>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 py-1 pointer-events-none">
                                Glisser ou <span class="font-semibold text-pink-500">uploader</span> \u00b7 Auto <span class="font-bold">WebP</span>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="px-6 pt-3">
                    <flux:input wire:model.live.debounce.300ms="mediaSearch" placeholder="Rechercher\u2026" icon="magnifying-glass" />
                </div>

                <div class="flex-1 overflow-y-auto px-6 pb-6 pt-3">
                    @if($this->pickerMedia->isEmpty())
                        <div class="flex flex-col items-center justify-center py-12 text-zinc-400">
                            <p class="text-sm font-semibold">Aucune image</p>
                            <p class="text-xs mt-1">Uploadez une image ci-dessus</p>
                        </div>
                    @else
                        <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-3">
                            @foreach($this->pickerMedia as $media)
                                <button type="button"
                                        wire:click="pickMedia({{ $media->id }})"
                                        class="group/media relative aspect-square rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border-2 border-transparent hover:border-pink-400 transition-all"
                                        title="{{ $media->original_name }}">
                                    <img src="{{ $media->url }}" alt="{{ $media->original_name }}"
                                         class="w-full h-full object-cover transition-transform duration-200 group-hover/media:scale-105">
                                    <div class="absolute inset-0 flex items-end justify-center pb-2 opacity-0 group-hover/media:opacity-100 transition">
                                        <span class="bg-pink-500 text-white text-[9px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full">Choisir</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                        @if($this->pickerMedia->hasPages())
                            <div class="pt-4">{{ $this->pickerMedia->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
'''

# Write file using the exact filename with Unicode character
filepath = os.path.join(r'c:\dev\schic\resources\views\components\admin\categories', '\u26a1index.blade.php')
with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print(f"Written to: {filepath}")
print(f"File size: {os.path.getsize(filepath)} bytes")
