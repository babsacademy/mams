# -*- coding: utf-8 -*-
import os

content = r'''<?php

use App\Models\Media;
use App\Services\ImageConverter;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('M''' + "\u00e9" + r'''diath''' + "\u00e8" + r'''que')] #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads, WithPagination;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $uploads = [];

    public string $search = '';

    public ?int $selectedId = null;

    public bool $isUploading = false;

    public bool $isDragging = false;

    /** @var array<string> */
    public array $uploadErrors = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUploads(): void
    {
        $this->processUploads();
    }

    private function processUploads(): void
    {
        $this->uploadErrors    = [];
        $this->isUploading = true;
        $converter       = new ImageConverter();
        $allowed         = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

        foreach ($this->uploads as $upload) {
            try {
                if (! in_array($upload->getMimeType(), $allowed, true)) {
                    $this->uploadErrors[] = "'{$upload->getClientOriginalName()}' : format non support''' + "\u00e9" + r'''.";
                    continue;
                }

                $tmpPath   = $upload->getRealPath();
                $webpPath  = $converter->toWebP($tmpPath);
                $dimensions = $converter->getDimensions($webpPath);

                $filename  = uniqid('media_', true) . '.webp';
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

<div x-data="{ dragging: false }" class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">M''' + "\u00e9" + r'''diath''' + "\u00e8" + r'''que</h1>
            <div class="flex items-center gap-3 mt-1.5 capitalize">
                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold">{{ $this->all_media->total() }} Fichiers</span>
                <span class="size-1 rounded-full bg-zinc-300"></span>
                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold">{{ $this->totalSize }} Utilis''' + "\u00e9" + r'''s</span>
            </div>
        </div>

        <div class="flex-1 max-w-md">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Chercher un fichier..." icon="magnifying-glass" variant="filled" class="!bg-white dark:!bg-zinc-900 border-zinc-200 dark:border-zinc-800 !h-12 font-bold" />
        </div>
    </div>

    {{-- Erreurs upload --}}
    @if($uploadErrors)
        <div class="rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 p-5 animate-in slide-in-from-top-2">
            <div class="flex items-center gap-3 mb-2">
                <flux:icon.exclamation-triangle class="size-4 text-rose-500" />
                <h4 class="text-xs font-black text-rose-600 uppercase tracking-widest">Erreurs lors de l'envoi</h4>
            </div>
            <ul class="space-y-1">
                @foreach($uploadErrors as $err)
                    <li class="text-xs text-rose-600/80 font-medium">{{ $err }}</li>
                @endforeach
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
                x-on:drop.prevent="dragging = false; $wire.uploadMultiple('uploads', Array.from($event.dataTransfer.files))"
                :class="dragging ? 'border-brand-pink bg-brand-pink/5 scale-[1.01]' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                class="relative border-2 border-dashed rounded-2xl p-6 text-center transition-all duration-300 cursor-pointer group overflow-hidden"
                x-on:click="$refs.fileInput.click()"
            >
                <div class="absolute inset-0 bg-gradient-to-br from-brand-pink/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>

                <input
                    x-ref="fileInput"
                    type="file"
                    class="hidden"
                    multiple
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    x-on:change="$wire.uploadMultiple('uploads', Array.from($refs.fileInput.files))"
                >

                @if($isUploading)
                    <div class="relative flex flex-col sm:flex-row items-center justify-center gap-4">
                        <div class="relative size-12 flex items-center justify-center shrink-0">
                            <div class="absolute inset-0 rounded-full border-[3px] border-zinc-100 dark:border-zinc-800"></div>
                            <div class="absolute inset-0 rounded-full border-[3px] border-brand-pink border-t-transparent animate-spin"></div>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Traitement WebP...</p>
                            <p class="text-[9px] text-zinc-500 uppercase tracking-widest font-bold mt-1">Optimisation des images en cours</p>
                        </div>
                    </div>
                @else
                    <div class="relative flex flex-col sm:flex-row items-center justify-center gap-4 pointer-events-none">
                        <div class="size-12 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500 shrink-0">
                            <flux:icon.arrow-up-tray class="size-6 text-zinc-400 group-hover:text-brand-pink transition-colors" />
                        </div>
                        <div class="text-left text-center sm:text-left">
                            <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">D''' + "\u00e9" + r'''posez vos visuels ici</p>
                            <p class="text-[9px] text-zinc-500 uppercase tracking-widest font-bold mt-1">Ou cliquez pour explorer vos fichiers</p>
                        </div>
                        <div class="flex items-center justify-center gap-2 mt-2 sm:mt-0 sm:ml-4">
                             <span class="px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-[8px] font-black text-zinc-500 uppercase rounded tracking-widest">WebP Auto</span>
                             <span class="px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-[8px] font-black text-zinc-500 uppercase rounded tracking-widest">Max 10Mo</span>
                        </div>
                    </div>
                @endif

                {{-- Decorative background --}}
                <div class="absolute -bottom-10 -right-10 size-40 bg-brand-pink/5 blur-3xl rounded-full"></div>
                <div class="absolute -top-10 -left-10 size-40 bg-brand-pink/5 blur-3xl rounded-full"></div>
            </div>

            {{-- Grille images --}}
            @if($this->all_media->isEmpty())
                <div class="flex flex-col items-center justify-center py-32 text-center">
                    <div class="size-16 bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-2xl flex items-center justify-center mb-6">
                        <flux:icon.photo class="size-8 text-zinc-200 dark:text-zinc-700" />
                    </div>
                    <h3 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Aucun m''' + "\u00e9" + r'''dia trouv''' + "\u00e9" + r'''</h3>
                    <p class="text-sm text-zinc-400 mt-2 max-w-xs transition-opacity">Utilisez la zone de d''' + "\u00e9" + r'''p''' + "\u00f4" + r'''t ci-dessus pour envoyer vos images.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    @foreach($this->all_media as $media)
                        <button
                            wire:click="selectMedia({{ $media->id }})"
                            type="button"
                            class="group relative aspect-square rounded-xl overflow-hidden bg-white dark:bg-zinc-900 border-4 transition-all duration-300 transform active:scale-95 shadow-sm
                                   {{ $selectedId === $media->id ? 'border-brand-pink ring-4 ring-brand-pink/10 translate-y-[-4px]' : 'border-zinc-100 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-600' }}"
                        >
                            <img src="{{ $media->url }}" alt="{{ $media->alt ?? $media->original_name }}"
                                 class="size-full object-cover transition-transform duration-700 group-hover:scale-110">

                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-3 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end h-1/2">
                                <p class="text-[9px] text-white font-black uppercase tracking-widest leading-tight truncate px-1">{{ $media->original_name }}</p>
                                <p class="text-[8px] text-brand-pink font-bold uppercase tracking-widest px-1 mt-0.5">{{ $media->formatted_size }}</p>
                            </div>

                            @if($selectedId === $media->id)
                                <div class="absolute top-2 right-2">
                                    <div class="size-6 rounded-full bg-brand-pink border-2 border-white dark:border-zinc-900 flex items-center justify-center shadow-lg">
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

        {{-- Panneau d''' + "\u00e9" + r'''tail --}}
        <div class="relative">
            <div class="sticky top-8 space-y-6">
                @if($this->selectedMedia)
                    @php $m = $this->selectedMedia; @endphp
                    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-300">
                        <div class="relative aspect-square border-b border-zinc-100 dark:border-zinc-800 group/zoom">
                            <img src="{{ $m->url }}" alt="{{ $m->alt ?? $m->original_name }}"
                                 class="size-full object-cover">
                            <a href="{{ $m->url }}" target="_blank" class="absolute top-4 right-4 size-10 bg-black/40 backdrop-blur-md rounded-2xl flex items-center justify-center text-white opacity-0 group-hover/zoom:opacity-100 transition-opacity">
                                <flux:icon.magnifying-glass-plus class="size-5" />
                            </a>
                        </div>

                        <div class="p-8 space-y-8">
                            <div>
                                <h4 class="text-[10px] text-zinc-400 uppercase tracking-[0.2em] font-black mb-2">Informations</h4>
                                <p class="text-sm font-black text-zinc-900 dark:text-white break-all leading-relaxed">{{ $m->original_name }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-6 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-zinc-100 dark:border-zinc-700/50">
                                <div>
                                    <h4 class="text-[8px] text-zinc-400 uppercase tracking-widest font-black mb-1">Format</h4>
                                    <p class="text-[10px] font-black text-zinc-700 dark:text-zinc-300 uppercase">{{ $m->width }}''' + "\u00d7" + r'''{{ $m->height }} px</p>
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
                                        <span x-show="copied" class="text-emerald-500">Pr''' + "\u00ea" + r'''t !</span>
                                    </button>
                                </div>
                            </div>

                            <flux:button
                                wire:click="deleteMedia({{ $m->id }})"
                                wire:confirm="Supprimer d''' + "\u00e9" + r'''finitivement cette image ?"
                                variant="danger"
                                class="w-full !bg-rose-500 hover:!bg-rose-600 border-none font-black uppercase tracking-widest text-[10px] py-4 rounded-2xl shadow-lg shadow-rose-500/20"
                                icon="trash"
                            >
                                Supprimer le m''' + "\u00e9" + r'''dia
                            </flux:button>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-800 p-12 text-center">
                        <div class="size-16 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <flux:icon.information-circle class="size-8 text-zinc-300" />
                        </div>
                        <h4 class="text-base font-black text-zinc-900 dark:text-white uppercase tracking-tight">D''' + "\u00e9" + r'''tails</h4>
                        <p class="text-xs text-zinc-400 mt-2 leading-relaxed">Cliquez sur un m''' + "\u00e9" + r'''dia pour consulter ses caract''' + "\u00e9" + r'''ristiques ou le supprimer.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
'''

filepath = os.path.join('c:', os.sep, 'dev', 'schic', 'resources', 'views', 'components', 'admin', 'media', '\u26a1library.blade.php')
with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print(f"Written to: {filepath}")
print(f"Size: {os.path.getsize(filepath)} bytes")
