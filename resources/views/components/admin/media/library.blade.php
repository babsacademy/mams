<?php

use App\Models\Media;
use App\Services\MediaUploader;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Médiathèque')] #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads, WithPagination;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $uploads = [];

    public string $search = '';

    public ?int $selectedId = null;

    public bool $isUploading = false;

    public bool $isDragging = false;

    public string $filter = 'all'; // 'all', 'images', 'videos'

    /** @var array<string> */
    public array $uploadErrors = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUploads(): void
    {
        Gate::authorize('admin-action');

        // Valide la taille de chaque fichier
        foreach ($this->uploads as $key => $upload) {
            $sizeMB = $upload->getSize() / (1024 * 1024);
            if ($sizeMB > 100) {
                $this->uploadErrors[] = "'{$upload->getClientOriginalName()}' dépasse 100 MB (size: ".round($sizeMB, 1)." MB)";
                unset($this->uploads[$key]);
            }
        }

        if (! empty($this->uploads)) {
            $this->processUploads();
        }
    }

    private function processUploads(): void
    {
        $this->uploadErrors = [];
        $this->isUploading = true;
        $uploader = new MediaUploader();

        foreach ($this->uploads as $upload) {
            try {
                $uploader->upload($upload);
            } catch (\Throwable $e) {
                $this->uploadErrors[] = "'{$upload->getClientOriginalName()}' : " . $e->getMessage();
            }
        }

        $this->uploads     = [];
        $this->isUploading = false;
        unset($this->all_media);
    }

    public function deleteMedia(int $mediaId): void
    {
        Gate::authorize('admin-action');

        $media = Media::findOrFail($mediaId);
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        if ($this->selectedId === $mediaId) {
            $this->selectedId = null;
        }

        unset($this->all_media);
    }

    public function selectMedia(int $mediaId): void
    {
        $this->selectedId = ($this->selectedId === $mediaId) ? null : $mediaId;
    }

    #[Computed]
    public function all_media(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Media::query()
            ->when($this->search, fn ($q) => $q->where('original_name', 'like', "%{$this->search}%"))
            ->when($this->filter !== 'all', fn ($q) => $q->where('type', $this->filter))
            ->latest()
            ->paginate(24);
    }

    #[Computed]
    public function selectedMedia(): ?Media
    {
        return $this->selectedId ? Media::find($this->selectedId) : null;
    }

    #[Computed]
    public function totalSize(): string
    {
        $bytes = Media::sum('size');

        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' Ko';
        }

        return round($bytes / 1048576, 2) . ' Mo';
    }
}; ?>

<div
    x-data="{
        dragging: false,
        maxUploadBatchSize: 15,
        uploadInBatches(files) {
            const selectedFiles = Array.from(files ?? []);

            if (selectedFiles.length === 0) {
                return;
            }

            const batches = [];

            for (let index = 0; index < selectedFiles.length; index += this.maxUploadBatchSize) {
                batches.push(selectedFiles.slice(index, index + this.maxUploadBatchSize));
            }

            const uploadBatch = (batchIndex) => {
                if (batchIndex >= batches.length) {
                    return;
                }

                $wire.uploadMultiple(
                    'uploads',
                    batches[batchIndex],
                    () => uploadBatch(batchIndex + 1),
                    () => uploadBatch(batchIndex + 1),
                );
            };

            uploadBatch(0);
        },
    }"
    class="space-y-8"
>
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Médiathèque</h1>
            <div class="flex items-center gap-3 mt-1.5 capitalize">
                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold">{{ $this->all_media->total() }} Fichiers</span>
                <span class="size-1 rounded-full bg-zinc-300"></span>
                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold">{{ $this->totalSize }} Utilisés</span>
            </div>
        </div>

        <div class="flex-1 max-w-md">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Chercher un fichier..." icon="magnifying-glass" variant="filled" class="!bg-white dark:!bg-zinc-900 border-zinc-200 dark:border-zinc-800 !h-12 font-bold" />
        </div>
    </div>

    {{-- Filtres par type --}}
    <div class="flex gap-3">
        <button wire:click="$set('filter', 'all')" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $filter === 'all' ? 'bg-brand-primary text-white shadow-lg' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
            Tous
        </button>
        <button wire:click="$set('filter', 'image')" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $filter === 'image' ? 'bg-brand-primary text-white shadow-lg' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
            Images
        </button>
        <button wire:click="$set('filter', 'video')" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $filter === 'video' ? 'bg-brand-primary text-white shadow-lg' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
            Vidéos
        </button>
    </div>

    {{-- Erreurs upload --}}
    @if($uploadErrors || $errors->has('uploads.*'))
        <div class="rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 p-5 animate-in slide-in-from-top-2">
            <div class="flex items-center gap-3 mb-2">
                <flux:icon.exclamation-triangle class="size-4 text-rose-500" />
                <h4 class="text-xs font-black text-rose-600 uppercase tracking-widest">Erreurs lors de l'envoi</h4>
            </div>
            <ul class="space-y-1">
                @foreach($uploadErrors as $err)
                    <li class="text-xs text-rose-600/80 font-medium">{{ $err }}</li>
                @endforeach
                @error('uploads.*')
                    <li class="text-xs text-rose-600/80 font-medium">{{ $message }}</li>
                @enderror
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
        {{-- Colonne principale --}}
        <div class="xl:col-span-3 space-y-8">

            {{-- Zone upload --}}
            <div
                x-on:dragover.prevent="dragging = true"
                x-on:dragleave.prevent="dragging = false"
                x-on:drop.prevent="dragging = false; uploadInBatches($event.dataTransfer.files)"
                :class="dragging ? 'border-brand-primary bg-brand-primary/5 scale-[1.01]' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                class="relative border-2 border-dashed rounded-[2rem] p-6 text-center transition-all duration-300 cursor-pointer group overflow-hidden"
                x-on:click="$refs.fileInput.click()"
            >
                <div class="absolute inset-0 bg-gradient-to-br from-brand-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                <input
                    x-ref="fileInput"
                    type="file"
                    class="hidden"
                    multiple
                    accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime"
                    x-on:change="uploadInBatches($refs.fileInput.files)"
                >
                
                @if($isUploading)
                    <div class="relative flex flex-col sm:flex-row items-center justify-center gap-4">
                        <div class="relative size-12 flex items-center justify-center shrink-0">
                            <div class="absolute inset-0 rounded-full border-[3px] border-zinc-100 dark:border-zinc-800"></div>
                            <div class="absolute inset-0 rounded-full border-[3px] border-brand-primary border-t-transparent animate-spin"></div>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Traitement WebP...</p>
                            <p class="text-[9px] text-zinc-500 uppercase tracking-widest font-bold mt-1">Optimisation des images en cours</p>
                        </div>
                    </div>
                @else
                    <div class="relative flex flex-col sm:flex-row items-center justify-center gap-4 pointer-events-none">
                        <div class="size-12 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500 shrink-0">
                            <flux:icon.arrow-up-tray class="size-6 text-zinc-400 group-hover:text-brand-primary transition-colors" />
                        </div>
                        <div class="text-left text-center sm:text-left">
                            <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Déposez vos visuels ici</p>
                            <p class="text-[9px] text-zinc-500 uppercase tracking-widest font-bold mt-1">Ou cliquez pour explorer vos fichiers</p>
                        </div>
                        <div class="flex items-center justify-center gap-2 mt-2 sm:mt-0 sm:ml-4">
                             <span class="px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-[8px] font-black text-zinc-500 uppercase rounded tracking-widest">WebP Auto</span>
                             <span class="px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-[8px] font-black text-zinc-500 uppercase rounded tracking-widest">Max 10Mo</span>
                        </div>
                    </div>
                @endif
                
                {{-- Decorative background --}}
                <div class="absolute -bottom-10 -right-10 size-40 bg-brand-primary/5 blur-3xl rounded-full"></div>
                <div class="absolute -top-10 -left-10 size-40 bg-brand-primary/5 blur-3xl rounded-full"></div>
            </div>

            {{-- Grille images --}}
            @if($this->all_media->isEmpty())
                <div class="flex flex-col items-center justify-center py-32 text-center">
                    <div class="size-20 bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-3xl flex items-center justify-center mb-6">
                        <flux:icon.photo class="size-10 text-zinc-200 dark:text-zinc-700" />
                    </div>
                    <h3 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Aucun média trouvé</h3>
                    <p class="text-sm text-zinc-400 mt-2 max-w-xs transition-opacity">Utilisez la zone de dépôt ci-dessus pour envoyer vos images.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    @foreach($this->all_media as $media)
                        <button
                            wire:click="selectMedia({{ $media->id }})"
                            type="button"
                            class="group relative aspect-square rounded-[1.5rem] overflow-hidden bg-white dark:bg-zinc-900 border-4 transition-all duration-300 transform active:scale-95 shadow-sm
                                   {{ $selectedId === $media->id ? 'border-brand-primary ring-4 ring-brand-primary/10 translate-y-[-4px]' : 'border-zinc-100 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-600' }}"
                        >
                            @if($media->isVideo())
                                <video src="{{ $media->url }}" class="size-full object-cover transition-transform duration-700 group-hover:scale-110"></video>
                                <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/40 transition-colors">
                                    <div class="size-12 bg-brand-primary rounded-full flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                        <flux:icon.play class="size-6 ml-0.5" />
                                    </div>
                                </div>
                            @else
                                <img src="{{ $media->url }}" alt="{{ $media->alt ?? $media->original_name }}"
                                     class="size-full object-cover transition-transform duration-700 group-hover:scale-110">
                            @endif

                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-3 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end h-1/2">
                                <p class="text-[9px] text-white font-black uppercase tracking-widest leading-tight truncate px-1">{{ $media->original_name }}</p>
                                <p class="text-[8px] text-brand-primary font-bold uppercase tracking-widest px-1 mt-0.5">{{ $media->formatted_size }}</p>
                            </div>

                            @if($selectedId === $media->id)
                                <div class="absolute top-2 right-2">
                                    <div class="size-6 rounded-full bg-brand-primary border-2 border-white dark:border-zinc-900 flex items-center justify-center shadow-lg">
                                        <flux:icon.check class="size-3.5 text-white font-black" />
                                    </div>
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>

                @if($this->all_media->hasPages())
                    <div class="pt-6">{{ $this->all_media->links() }}</div>
                @endif
            @endif
        </div>

        {{-- Panneau détail --}}
        <div class="relative">
            <div class="sticky top-8 space-y-6">
                @if($this->selectedMedia)
                    @php $m = $this->selectedMedia; @endphp
                    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-300">
                        <div class="relative aspect-square border-b border-zinc-100 dark:border-zinc-800 group/zoom bg-black">
                            @if($m->isVideo())
                                <video src="{{ $m->url }}" controls class="size-full object-cover"></video>
                            @else
                                <img src="{{ $m->url }}" alt="{{ $m->alt ?? $m->original_name }}"
                                     class="size-full object-cover">
                                <a href="{{ $m->url }}" target="_blank" class="absolute top-4 right-4 size-10 bg-black/40 backdrop-blur-md rounded-2xl flex items-center justify-center text-white opacity-0 group-hover/zoom:opacity-100 transition-opacity">
                                    <flux:icon.magnifying-glass-plus class="size-5" />
                                </a>
                            @endif
                        </div>
                        
                        <div class="p-8 space-y-8">
                            <div>
                                <h4 class="text-[10px] text-zinc-400 uppercase tracking-[0.2em] font-black mb-2">Informations</h4>
                                <p class="text-sm font-black text-zinc-900 dark:text-white break-all leading-relaxed">{{ $m->original_name }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-6 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-zinc-100 dark:border-zinc-700/50">
                                <div>
                                    <h4 class="text-[8px] text-zinc-400 uppercase tracking-widest font-black mb-1">Format</h4>
                                    <p class="text-[10px] font-black text-zinc-700 dark:text-zinc-300 uppercase">{{ $m->width }}×{{ $m->height }} px</p>
                                </div>
                                <div>
                                    <h4 class="text-[8px] text-zinc-400 uppercase tracking-widest font-black mb-1">Poids</h4>
                                    <p class="text-[10px] font-black text-zinc-700 dark:text-zinc-300 uppercase">{{ $m->formatted_size }}</p>
                                </div>
                                <div class="col-span-2">
                                    <h4 class="text-[8px] text-zinc-400 uppercase tracking-widest font-black mb-1">Date d'import</h4>
                                    <p class="text-[10px] font-black text-zinc-700 dark:text-zinc-300 uppercase">{{ $m->created_at->format('d M Y') }}</p>
                                </div>
                            </div>

                            <div class="space-y-3" x-data="{ copied: false }">
                                <h4 class="text-[10px] text-zinc-400 uppercase tracking-[0.2em] font-black">Lien direct</h4>
                                <div class="flex gap-2 p-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-2xl border border-zinc-200/50 dark:border-zinc-700/50">
                                    <input type="text" readonly value="{{ $m->url }}"
                                           class="flex-1 text-[10px] bg-transparent border-none focus:ring-0 text-zinc-500 font-bold px-3 truncate"
                                           x-on:click="$el.select()">
                                    <button
                                        type="button"
                                        class="px-4 py-2 bg-white dark:bg-zinc-700 text-[10px] font-black uppercase tracking-widest text-zinc-900 dark:text-white rounded-xl shadow-sm hover:scale-[1.02] active:scale-95 transition-all"
                                        x-on:click="navigator.clipboard.writeText('{{ $m->url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    >
                                        <span x-show="!copied">Copier</span>
                                        <span x-show="copied" class="text-emerald-500">Prêt !</span>
                                    </button>
                                </div>
                            </div>

                            <flux:button
                                wire:click="deleteMedia({{ $m->id }})"
                                wire:confirm="Supprimer définitivement cette image ?"
                                variant="danger"
                                class="w-full !bg-rose-500 hover:!bg-rose-600 border-none font-black uppercase tracking-widest text-[10px] py-4 rounded-2xl shadow-lg shadow-rose-500/20"
                                icon="trash"
                            >
                                Supprimer le média
                            </flux:button>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border-2 border-dashed border-zinc-200 dark:border-zinc-800 p-12 text-center">
                        <div class="size-16 bg-zinc-50 dark:bg-zinc-800 rounded-3xl flex items-center justify-center mx-auto mb-6">
                            <flux:icon.information-circle class="size-8 text-zinc-300" />
                        </div>
                        <h4 class="text-base font-black text-zinc-900 dark:text-white uppercase tracking-tight">Détails</h4>
                        <p class="text-xs text-zinc-400 mt-2 leading-relaxed">Cliquez sur un média pour consulter ses caractéristiques ou le supprimer.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
