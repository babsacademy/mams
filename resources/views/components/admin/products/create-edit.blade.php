<?php

use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Services\ImageConverter;
use Illuminate\Support\Facades\Gate;
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

    public string $name           = '';
    public string $lengthLabel    = '';
    public string $colorLabel     = '';
    public string $description    = '';
    public string $price          = '';
    public string $originalPrice  = '';
    public string $stock          = '0';
    public string $imageUrl       = '';
    public string $badge          = '';
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

    // Galerie produit
    public bool $showGalleryPicker = false;
    public string $gallerySearch   = '';
    public int $galleryMaxItems    = 5;

    public function mount(?Product $product = null): void
    {
        if ($product?->exists) {
            $this->product       = $product;
            $this->name          = $product->name;
            $this->lengthLabel   = $product->length_label ?? '';
            $this->colorLabel    = $product->color_label ?? '';
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
        Gate::authorize('admin-action');

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
        Gate::authorize('admin-action');

        $media          = Media::findOrFail($mediaId);
        $this->imageUrl = $media->url;
        $this->showMediaPicker = false;
        $this->mediaSearch     = '';
    }

    public function clearImage(): void
    {
        Gate::authorize('admin-action');

        $this->imageUrl = '';
    }

    public function attachMedia(int $mediaId): void
    {
        Gate::authorize('admin-action');

        if (!$this->product?->exists) {
            return;
        }

        $this->product->media()->syncWithoutDetaching([$mediaId]);
        $this->showGalleryPicker = false;
        $this->gallerySearch = '';
    }

    public function detachMedia(int $mediaId): void
    {
        Gate::authorize('admin-action');

        if (!$this->product?->exists) {
            return;
        }

        $this->product->media()->detach($mediaId);
    }

    public function updatedGallerySearch(): void
    {
        $this->resetPage('galleryPage');
    }

    public function save(): void
    {
        Gate::authorize('admin-action');

        $urlValidation = $this->urlMode === 'external'
            ? ['nullable', 'url', 'max:500']
            : ['nullable', 'string', 'max:500'];

        $validated = $this->validate([
            'name'          => ['required', 'string', 'max:255'],
            'lengthLabel'   => ['nullable', 'string', 'max:50'],
            'colorLabel'    => ['nullable', 'string', 'max:100'],
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
            'length_label'   => $validated['lengthLabel'] ?: null,
            'color_label'    => $validated['colorLabel'] ?: null,
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

    #[Computed]
    public function productMedia(): \Illuminate\Support\Collection
    {
        return $this->product?->media ?? collect();
    }

    #[Computed]
    public function galleryPickerMedia(): \Illuminate\Pagination\LengthAwarePaginator
    {
        if (!$this->product?->exists) {
            return Media::query()->paginate(0);
        }

        return Media::query()
            ->whereNotIn('id', $this->productMedia->pluck('id'))
            ->when($this->gallerySearch, fn ($q) => $q->where('original_name', 'like', "%{$this->gallerySearch}%"))
            ->latest()
            ->paginate(18, pageName: 'galleryPage');
    }

    public function isEditing(): bool
    {
        return $this->product?->exists === true;
    }
}; ?>

<div>
    <div class="mb-10 flex items-center justify-between gap-4">
        <div class="flex items-center gap-5">
            <flux:button href="{{ route('admin.products.index') }}" variant="ghost" icon="arrow-left" inset class="text-zinc-400 hover:text-white" />
            <div>
                <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">
                    {{ $this->isEditing() ? 'Modifier Produit' : 'Nouveau Produit' }}
                </h1>
                <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">
                    {{ $this->isEditing() ? 'Édition de : ' . $name : 'Ajoutez une nouvelle pièce à votre collection.' }}
                </p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            {{-- Informations --}}
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8 space-y-8">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-lg bg-brand-primary/10 flex items-center justify-center">
                        <flux:icon.pencil-square class="size-4 text-brand-primary" />
                    </div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Détails généraux</h2>
                </div>

                <div class="space-y-6">
                    <flux:input wire:model="name" label="Désignation" placeholder="Ex : Le Signature Noir" variant="filled" class="!h-12 !font-bold" required />
                    
                    <flux:textarea wire:model="description" label="Description détaillée" rows="6" placeholder="Spécifications, matières, histoire du produit…" variant="filled" />
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <flux:input wire:model="price" label="Prix de vente (FCFA)" type="number" min="0" variant="filled" class="!h-12 !font-black text-lg" required />
                        <flux:input wire:model="originalPrice" label="Prix d'origine / barré" type="number" min="0" variant="filled" class="!h-12 text-zinc-400" placeholder="Laisse vide si pas de promo" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <flux:input wire:model="stock" label="Stock disponible" type="number" min="0" variant="filled" class="!h-12 !font-bold" required />
                        <flux:input wire:model="badge" label="Badge promotionnel" placeholder="Ex : -20% ou LIMITED" variant="filled" class="!h-12" />
                    </div>
                </div>
            </div>

            {{-- Image --}}
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8 space-y-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-lg bg-brand-primary/10 flex items-center justify-center">
                            <flux:icon.photo class="size-4 text-brand-primary" />
                        </div>
                        <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Visuel produit</h2>
                    </div>

                    <div class="flex items-center p-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                        <button type="button"
                                wire:click="$set('urlMode', 'media')"
                                class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest transition rounded-lg {{ $urlMode === 'media' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                            Médiathèque
                        </button>
                        <button type="button"
                                wire:click="$set('urlMode', 'external')"
                                class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest transition rounded-lg {{ $urlMode === 'external' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                            Lien Direct
                        </button>
                    </div>
                </div>

                @if($urlMode === 'media')
                    @if($imageUrl)
                        <div class="relative group rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 aspect-video bg-zinc-50 dark:bg-zinc-900">
                            <img src="{{ $imageUrl }}" alt="Preview" class="size-full object-cover">
                            <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-4">
                                <flux:button type="button" wire:click="$set('showMediaPicker', true)" size="sm" variant="filled" class="!bg-white !text-zinc-900 !font-black uppercase tracking-widest text-[10px]">
                                    Modifier
                                </flux:button>
                                <flux:button type="button" wire:click="clearImage" size="sm" variant="danger" class="!bg-rose-500 !font-black uppercase tracking-widest text-[10px]">
                                    Supprimer
                                </flux:button>
                            </div>
                        </div>
                    @else
                        <button type="button"
                                wire:click="$set('showMediaPicker', true)"
                                class="w-full h-64 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl p-12 text-center hover:border-brand-primary/50 hover:bg-brand-primary/5 transition group flex flex-col items-center justify-center gap-5">
                            <div class="size-16 rounded-2xl bg-zinc-100 dark:bg-zinc-800 group-hover:bg-brand-primary/10 flex items-center justify-center transition-colors">
                                <flux:icon.plus class="size-8 text-zinc-400 group-hover:text-brand-primary transition-colors" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Ajouter un visuel</p>
                                <p class="text-xs text-zinc-400">Parcourez votre médiathèque ou importez un fichier.</p>
                            </div>
                        </button>
                    @endif
                @else
                    <div class="space-y-4">
                        <flux:input wire:model="imageUrl" label="URL de l'image" placeholder="https://votre-image.com/photo.jpg" variant="filled" />
                        @if($imageUrl)
                            <div class="rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 aspect-video">
                                <img src="{{ $imageUrl }}" alt="Aperçu externe" class="size-full object-cover">
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Galerie & Vidéos --}}
            @if($this->isEditing())
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8 space-y-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-lg bg-brand-primary/10 flex items-center justify-center">
                            <flux:icon.film class="size-4 text-brand-primary" />
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Galerie & Vidéos</h2>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold mt-0.5">Ajoutez jusqu'à {{ $galleryMaxItems }} médias</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">{{ $this->productMedia->count() }}/{{ $galleryMaxItems }}</span>
                </div>

                {{-- Médias actuels --}}
                @if($this->productMedia->isNotEmpty())
                    <div class="space-y-3">
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Médias associés</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            @foreach($this->productMedia as $media)
                                <div class="group relative rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 aspect-square">
                                    @if($media->type === 'video')
                                        <div class="size-full bg-black/50 flex items-center justify-center">
                                            <flux:icon.play class="size-8 text-white" />
                                        </div>
                                    @else
                                        <img src="{{ $media->url }}" alt="{{ $media->original_name }}" class="size-full object-cover">
                                    @endif

                                    @if($media->type === 'video')
                                        <span class="absolute top-2 left-2 bg-red-500 text-white text-[8px] font-black px-2 py-1 rounded uppercase">Vidéo</span>
                                    @endif

                                    <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                                        <button type="button"
                                                wire:click="detachMedia({{ $media->id }})"
                                                wire:confirm="Retirer ce média ?"
                                                class="bg-rose-500 hover:bg-rose-600 text-white px-3 py-1.5 text-[10px] font-black uppercase rounded transition">
                                            Retirer
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Bouton ajouter --}}
                @if($this->productMedia->count() < $galleryMaxItems)
                    <div>
                        <button type="button"
                                wire:click="$set('showGalleryPicker', true)"
                                class="w-full h-40 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl p-8 text-center hover:border-brand-primary/50 hover:bg-brand-primary/5 transition group flex flex-col items-center justify-center gap-3">
                            <div class="size-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 group-hover:bg-brand-primary/10 flex items-center justify-center transition-colors">
                                <flux:icon.plus class="size-6 text-zinc-400 group-hover:text-brand-primary transition-colors" />
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Ajouter un média</p>
                                <p class="text-xs text-zinc-400">Image ou vidéo</p>
                            </div>
                        </button>
                    </div>
                @endif
            </div>
            @endif
        </div>

        <div class="space-y-8">
            {{-- Organisation --}}
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8 space-y-8">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-lg bg-brand-primary/10 flex items-center justify-center">
                        <flux:icon.tag class="size-4 text-brand-primary" />
                    </div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Classification</h2>
                </div>

                <flux:select wire:model="categoryId" label="Catégorie" class="!h-12 !font-bold">
                    <flux:select.option value="">Choisir une catégorie…</flux:select.option>
                    @foreach($this->categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8 space-y-8">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-lg bg-brand-primary/10 flex items-center justify-center">
                        <flux:icon.arrows-up-down class="size-4 text-brand-primary" />
                    </div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Guides client</h2>
                </div>

                <div class="space-y-6">
                    <flux:input wire:model="lengthLabel" label="Longueur / Taille" placeholder='Ex : 28" ou L30' variant="filled" class="!h-12 !font-bold" />
                    <flux:input wire:model="colorLabel" label="Coloris" placeholder="Ex : Noir & Marron" variant="filled" class="!h-12" />
                </div>
            </div>

            {{-- Visibilité --}}
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8 space-y-8">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-lg bg-brand-primary/10 flex items-center justify-center">
                        <flux:icon.eye class="size-4 text-brand-primary" />
                    </div>
                    <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Mise en avant</h2>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-700/50">
                        <div class="space-y-0.5">
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">Produit Actif</p>
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
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">Nouveauté</p>
                            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold">Bandeau spécifique</p>
                        </div>
                        <flux:switch wire:model="isNew" />
                    </div>
                </div>
            </div>

            <flux:button type="submit" variant="primary" class="w-full !h-14 font-black uppercase tracking-widest !bg-brand-primary border-none hover:shadow-brand-primary/30 shadow-xl" icon="check">
                {{ $this->isEditing() ? 'Sauvegarder les modifications' : 'Publier le produit' }}
            </flux:button>
        </div>
    </form>

    {{-- Modal médiathèque --}}
    @if($showMediaPicker)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-6"
             x-data
             x-on:keydown.escape.window="$wire.set('showMediaPicker', false)">

            <div class="absolute inset-0 bg-zinc-950/80 backdrop-blur-md transition-opacity"
                 wire:click="$set('showMediaPicker', false)"></div>

            <div class="relative bg-white dark:bg-zinc-900 rounded-[2rem] shadow-2xl w-full max-w-5xl max-h-[85vh] flex flex-col overflow-hidden border border-zinc-200 dark:border-zinc-800 transition-all transform animate-in zoom-in-95 duration-300">

                {{-- Header --}}
                <div class="flex items-center justify-between px-10 py-8 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h2 class="text-2xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Médiathèque</h2>
                        <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold mt-1">Sélectionnez ou uploadez vos visuels</p>
                    </div>
                    <button type="button" wire:click="$set('showMediaPicker', false)"
                            class="size-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 flex items-center justify-center transition-colors">
                        <flux:icon.x-mark class="size-5 text-zinc-500" />
                    </button>
                </div>

                {{-- Upload zone --}}
                <div class="px-10 pt-6"
                     x-data="{
                         dragging: false,
                         uploadInBatches(files) {
                             const selectedFiles = Array.from(files ?? []);

                             if (selectedFiles.length === 0) {
                                 return;
                             }

                             const batches = [];

                             for (let index = 0; index < selectedFiles.length; index += 15) {
                                 batches.push(selectedFiles.slice(index, index + 15));
                             }

                             const uploadBatch = (batchIndex) => {
                                 if (batchIndex >= batches.length) {
                                     return;
                                 }

                                 $wire.uploadMultiple(
                                     'mediaUploads',
                                     batches[batchIndex],
                                     () => uploadBatch(batchIndex + 1),
                                     () => uploadBatch(batchIndex + 1),
                                 );
                             };

                             uploadBatch(0);
                         },
                     }"
                     x-on:dragover.prevent="dragging = true"
                     x-on:dragleave.prevent="dragging = false"
                     x-on:drop.prevent="dragging = false; uploadInBatches($event.dataTransfer.files)">
                    <div
                        :class="dragging ? 'border-brand-primary bg-brand-primary/5' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30 hover:bg-zinc-100 dark:hover:bg-zinc-800/50'"
                        class="border-2 border-dashed rounded-2xl px-10 py-6 text-center transition-all cursor-pointer group"
                        x-on:click="$refs.pickerInput.click()"
                    >
                        <input x-ref="pickerInput" type="file" class="hidden" multiple
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               x-on:change="uploadInBatches($refs.pickerInput.files)">
                        
                        @if($isUploadingMedia)
                            <div class="flex items-center justify-center gap-4 py-2">
                                <div class="size-5 border-2 border-brand-primary border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Traitement & Conversion WebP…</span>
                            </div>
                        @else
                            <div class="flex items-center justify-center gap-4 py-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                <flux:icon.cloud-arrow-up class="size-6 text-brand-primary" />
                                <span class="text-sm text-zinc-500 dark:text-zinc-400 font-bold uppercase tracking-widest text-[10px]">Glisser ou cliqquer pour <span class="text-brand-primary">importer</span></span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Recherche --}}
                <div class="px-10 pt-6">
                    <flux:input wire:model.live.debounce.300ms="mediaSearch" placeholder="Chercher dans vos fichiers…" icon="magnifying-glass" variant="filled" class="!h-10 !text-xs font-bold" />
                </div>

                {{-- Grille --}}
                <div class="flex-1 overflow-y-auto px-10 pb-8 pt-6">
                    @if($this->pickerMedia->isEmpty())
                        <div class="flex flex-col items-center justify-center py-20 text-zinc-300 dark:text-zinc-700">
                            <flux:icon.photo class="size-20 mb-4 opacity-10" />
                            <p class="text-sm font-black uppercase tracking-widest">Aucun résultat</p>
                            <p class="text-[10px] mt-1 font-bold">Importez votre première image pour commencer.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-7 gap-4">
                            @foreach($this->pickerMedia as $media)
                                <button
                                    type="button"
                                    wire:click="pickMedia({{ $media->id }})"
                                    class="group relative aspect-square rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border-2 border-transparent hover:border-brand-primary transition-all transform hover:scale-[1.03]"
                                    title="{{ $media->original_name }}"
                                >
                                    <img src="{{ $media->url }}" alt="{{ $media->original_name }}"
                                         class="size-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-brand-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <div class="size-10 rounded-full bg-white flex items-center justify-center shadow-lg transform translate-y-2 group-hover:translate-y-0 transition-transform">
                                            <flux:icon.check class="size-5 text-brand-primary" />
                                        </div>
                                    </div>
                                    <div class="absolute bottom-0 left-0 right-0 p-1.5 bg-gradient-to-t from-black/60 to-transparent">
                                        <p class="text-[8px] font-bold text-white truncate text-center uppercase tracking-tighter opacity-70">{{ $media->width }}x{{ $media->height }}</p>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                        @if($this->pickerMedia->hasPages())
                            <div class="pt-8 flex justify-center">{{ $this->pickerMedia->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Modal galerie produit --}}
    @if($showGalleryPicker)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-6"
             x-data
             x-on:keydown.escape.window="$wire.set('showGalleryPicker', false)">

            <div class="absolute inset-0 bg-zinc-950/80 backdrop-blur-md transition-opacity"
                 wire:click="$set('showGalleryPicker', false)"></div>

            <div class="relative bg-white dark:bg-zinc-900 rounded-[2rem] shadow-2xl w-full max-w-5xl max-h-[85vh] flex flex-col overflow-hidden border border-zinc-200 dark:border-zinc-800 transition-all transform animate-in zoom-in-95 duration-300">

                {{-- Header --}}
                <div class="flex items-center justify-between px-10 py-8 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h2 class="text-2xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Ajouter un média</h2>
                        <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold mt-1">Images ou vidéos disponibles</p>
                    </div>
                    <button type="button" wire:click="$set('showGalleryPicker', false)"
                            class="size-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 flex items-center justify-center transition-colors">
                        <flux:icon.x-mark class="size-5 text-zinc-500" />
                    </button>
                </div>

                {{-- Recherche --}}
                <div class="px-10 pt-6">
                    <flux:input wire:model.live.debounce.300ms="gallerySearch" placeholder="Chercher dans vos fichiers…" icon="magnifying-glass" variant="filled" class="!h-10 !text-xs font-bold" />
                </div>

                {{-- Grille --}}
                <div class="flex-1 overflow-y-auto px-10 pb-8 pt-6">
                    @if($this->galleryPickerMedia->isEmpty())
                        <div class="flex flex-col items-center justify-center py-20 text-zinc-300 dark:text-zinc-700">
                            <flux:icon.photo class="size-20 mb-4 opacity-10" />
                            <p class="text-sm font-black uppercase tracking-widest">Aucun résultat</p>
                            <p class="text-[10px] mt-1 font-bold">Tous les médias sont déjà associés ou aucun média n'existe.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-7 gap-4">
                            @foreach($this->galleryPickerMedia as $media)
                                <button
                                    type="button"
                                    wire:click="attachMedia({{ $media->id }})"
                                    class="group relative aspect-square rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border-2 border-transparent hover:border-brand-primary transition-all transform hover:scale-[1.03]"
                                    title="{{ $media->original_name }}"
                                >
                                    @if($media->type === 'video')
                                        <div class="size-full bg-black/50 flex items-center justify-center">
                                            <flux:icon.play class="size-6 text-white" />
                                        </div>
                                        <span class="absolute top-1 left-1 bg-red-500 text-white text-[7px] font-black px-1.5 py-0.5 rounded-sm uppercase tracking-tighter">V</span>
                                    @else
                                        <img src="{{ $media->url }}" alt="{{ $media->original_name }}"
                                             class="size-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    @endif
                                    <div class="absolute inset-0 bg-brand-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <div class="size-10 rounded-full bg-white flex items-center justify-center shadow-lg transform translate-y-2 group-hover:translate-y-0 transition-transform">
                                            <flux:icon.plus class="size-5 text-brand-primary" />
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                        @if($this->galleryPickerMedia->hasPages())
                            <div class="pt-8 flex justify-center">{{ $this->galleryPickerMedia->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
