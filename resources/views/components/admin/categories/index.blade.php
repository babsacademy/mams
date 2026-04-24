<?php

use App\Models\Category;
use App\Models\Media;
use App\Services\ImageConverter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Catégories')] #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public ?int $editingId       = null;
    public string $name          = '';
    public string $imageUrl      = '';
    public bool $isActive        = true;
    public bool $showForm        = false;
    public bool $showDeleteModal = false;
    public ?int $confirmDeleteId = null;

    // Médiathèque
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
        Gate::authorize('admin-action');

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
        Gate::authorize('admin-action');

        $media                 = Media::findOrFail($mediaId);
        $this->imageUrl        = $media->url;
        $this->showMediaPicker = false;
        $this->mediaSearch     = '';
    }

    public function save(): void
    {
        Gate::authorize('admin-action');

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
            $this->dispatch('notify', message: 'Catégorie mise à jour.');
        } else {
            Category::create($data);
            $this->dispatch('notify', message: 'Catégorie créée.');
        }

        $this->showForm = false;
        $this->reset(['editingId', 'name', 'imageUrl', 'isActive']);
    }

    public function toggleActive(int $categoryId): void
    {
        Gate::authorize('admin-action');

        $category = Category::findOrFail($categoryId);
        $category->update(['is_active' => ! $category->is_active]);
    }

    public function confirmDelete(int $categoryId): void
    {
        $this->confirmDeleteId = $categoryId;
        $this->showDeleteModal = true;
    }

    public function deleteCategory(): void
    {
        Gate::authorize('admin-action');

        if ($this->confirmDeleteId) {
            Category::findOrFail($this->confirmDeleteId)->delete();
            $this->confirmDeleteId = null;
            $this->showDeleteModal = false;
            $this->dispatch('notify', message: 'Catégorie supprimée.');
        }
    }

    public function closeDeleteModal(): void
    {
        $this->confirmDeleteId = null;
        $this->showDeleteModal = false;
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
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Catégories</h1>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Organisez et classifiez vos produits par gammes.</p>
        </div>
        <flux:button wire:click="openCreate" variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px] py-3 shadow-lg shadow-brand-primary/20">
            <flux:icon.plus class="size-4 mr-2" />
            Nouvelle catégorie
        </flux:button>
    </div>

    {{-- Formulaire --}}
    @if($showForm)
        <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-8 mb-8">
            <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight mb-6">
                {{ $editingId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
            </h2>
            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="name" label="Nom" placeholder="Ex : Sacs à main" required />

                {{-- Image picker --}}
                <div>
                    <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-2">Image</p>
                    @if($imageUrl)
                        <div class="flex items-center gap-4">
                            <img src="{{ $imageUrl }}" alt="Aperçu" class="w-20 h-20 object-cover rounded-xl border border-zinc-200 dark:border-zinc-700">
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
                                class="w-full border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl p-6 text-center hover:border-brand-primary/50 hover:bg-brand-primary/5 dark:hover:bg-brand-primary/5 transition group">
                            <div class="flex items-center justify-center gap-2 pointer-events-none">
                                <flux:icon.photo class="w-5 h-5 text-zinc-400 group-hover:text-brand-primary transition" />
                                <span class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 group-hover:text-brand-primary transition">Choisir depuis la médiathèque</span>
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

    {{-- Liste --}}
    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-800">
                        <th class="text-left pl-8 pr-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.1em] whitespace-nowrap">Catégorie</th>
                        <th class="text-center px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.1em] hidden sm:table-cell whitespace-nowrap">Produits</th>
                        <th class="text-center px-6 py-5 text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.1em] whitespace-nowrap">Statut</th>
                        <th class="text-right pl-6 pr-8 py-5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                    @forelse($this->categories as $category)
                        <tr class="group hover:bg-zinc-50/80 dark:hover:bg-zinc-800/30 transition-colors duration-200">
                            <td class="pl-8 pr-6 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="relative size-14 rounded-xl overflow-hidden shrink-0 border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                                        @if($category->image_url)
                                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}"
                                                 class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                                        @else
                                            <div class="size-full flex items-center justify-center">
                                                <flux:icon.photo class="size-5 text-zinc-400 opacity-50" />
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-brand-primary transition-colors">{{ $category->name }}</span>
                                        <p class="text-[10px] text-zinc-400 tracking-wider mt-0.5 font-mono">/{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center hidden sm:table-cell whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-xs font-semibold rounded-full border border-zinc-200/50 dark:border-zinc-700/50">
                                    <flux:icon.tag class="size-3 opacity-50" />
                                    {{ $category->products_count }} {{ Str::plural('produit', $category->products_count) }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-center whitespace-nowrap">
                                <div class="flex justify-center">
                                    <flux:switch wire:click="toggleActive({{ $category->id }})" :checked="$category->is_active" size="sm" color="pink" />
                                </div>
                            </td>
                            <td class="pl-6 pr-8 py-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <flux:button wire:click="openEdit({{ $category->id }})" size="sm" variant="ghost" icon="pencil" inset class="text-zinc-400 hover:text-brand-primary" />
                                    <flux:button wire:click="confirmDelete({{ $category->id }})" size="sm" variant="ghost" icon="trash" inset class="text-zinc-400 hover:text-red-500" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <flux:icon.folder-open class="size-10 text-zinc-200 dark:text-zinc-800 mx-auto mb-4" />
                                <p class="text-zinc-500 dark:text-zinc-400 font-medium">Aucune catégorie trouvée.</p>
                                <p class="text-xs text-zinc-400 mt-1">Commencez par organiser votre boutique.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal suppression --}}
    <flux:modal name="confirm-delete-category" wire:model="showDeleteModal">
        <div class="p-6">
            <flux:heading size="lg">Supprimer la catégorie ?</flux:heading>
            <flux:subheading class="mt-2">Les produits associés seront aussi supprimés.</flux:subheading>
            <div class="flex justify-end gap-3 mt-6">
                <flux:button wire:click="closeDeleteModal" variant="ghost">Annuler</flux:button>
                <flux:button wire:click="deleteCategory" variant="danger">Supprimer</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal médiathèque --}}
    @if($showMediaPicker)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data
             x-on:keydown.escape.window="$wire.set('showMediaPicker', false)">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                 wire:click="$set('showMediaPicker', false)"></div>

            <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h2 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-widest">Médiathèque</h2>
                    <button type="button" wire:click="$set('showMediaPicker', false)"
                            class="size-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 flex items-center justify-center transition">
                        <flux:icon.x-mark class="size-4 text-zinc-500" />
                    </button>
                </div>

                <div class="px-6 pt-4"
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
                    <div :class="dragging ? 'border-brand-primary bg-brand-primary/5' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 hover:bg-zinc-100 dark:hover:bg-zinc-800'"
                         class="border-2 border-dashed rounded-xl px-6 py-3 text-center transition-colors cursor-pointer"
                         x-on:click="$refs.catInput.click()">
                        <input x-ref="catInput" type="file" class="hidden" multiple
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               x-on:change="uploadInBatches($refs.catInput.files)">
                        @if($isUploadingMedia)
                            <div class="flex items-center justify-center gap-2 py-1">
                                <div class="size-4 border-2 border-brand-primary border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-sm text-zinc-600 dark:text-zinc-400 font-semibold">Conversion WebP…</span>
                            </div>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 py-1 pointer-events-none">
                                Glisser ou <span class="font-semibold text-brand-primary">uploader</span> · Auto <span class="font-bold">WebP</span>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="px-6 pt-3">
                    <flux:input wire:model.live.debounce.300ms="mediaSearch" placeholder="Rechercher…" icon="magnifying-glass" />
                </div>

                <div class="flex-1 overflow-y-auto px-6 pb-6 pt-3">
                    @if($this->pickerMedia->isEmpty())
                        <div class="flex flex-col items-center justify-center py-12 text-zinc-400 dark:text-zinc-500">
                            <flux:icon.photo class="size-10 mb-3 opacity-30" />
                            <p class="text-sm font-semibold">Aucune image</p>
                            <p class="text-xs mt-1">Uploadez une image ci-dessus</p>
                        </div>
                    @else
                        <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-3">
                            @foreach($this->pickerMedia as $media)
                                <button type="button"
                                        wire:click="pickMedia({{ $media->id }})"
                                        class="group relative aspect-square rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border-2 border-transparent hover:border-brand-primary transition-all transform hover:scale-[1.03]"
                                        title="{{ $media->original_name }}">
                                    <img src="{{ $media->url }}" alt="{{ $media->original_name }}"
                                         class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105">
                                    <div class="absolute inset-0 bg-brand-primary/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                        <span class="bg-brand-primary text-white text-[9px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full">Choisir</span>
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
