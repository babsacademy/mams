<?php

use App\Models\Setting;
use App\Services\ImageConverter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component {
    use WithFileUploads, WithPagination;
    // Général
    public string $siteName = '';
    public string $siteTagline = '';
    public string $siteDescription = '';
    public string $logoUrl = '';
    public string $footerLogoUrl = '';
    public string $faviconUrl = '';
    public bool $showLogoPicker = false;
    public string $logoTarget = 'main';
    public string $logoSearch = '';
    public bool $isUploadingLogo = false;
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $logoUploads = [];

    // Contact
    public string $whatsappNumber = '';
    public string $phone = '';

    // Réseaux sociaux
    public string $socialInstagram = '';
    public string $socialFacebook = '';
    public string $socialTiktok = '';

    // Livraison
    public string $freeShippingThreshold = '';

    public string $successMessage = '';

    public function mount(): void
    {
        $this->siteName = Setting::get('site_name', Setting::get('shop_name', config('app.name', 'Laravel')));
        $this->siteTagline = Setting::get('site_tagline', '');
        $this->siteDescription = Setting::get('site_description', '');
        $this->logoUrl = Setting::get('logo_url', '');
        $this->footerLogoUrl = Setting::get('footer_logo_url', '');
        $this->faviconUrl = Setting::get('favicon_url', '');
        $this->whatsappNumber = Setting::get('whatsapp_number', '');
        $this->phone = Setting::get('phone_primary', Setting::get('phone', ''));
        $this->socialInstagram = Setting::get('instagram_url', Setting::get('social_instagram', ''));
        $this->socialFacebook = Setting::get('facebook_url', Setting::get('social_facebook', ''));
        $this->socialTiktok = Setting::get('tiktok_url', Setting::get('social_tiktok', ''));
        $this->freeShippingThreshold = Setting::get('free_shipping_threshold', '75000');
    }

    public function updatedLogoSearch(): void
    {
        $this->resetPage('logoPage');
    }

    public function updatedLogoUploads(): void
    {
        Gate::authorize('admin-action');

        $this->isUploadingLogo = true;
        $converter = new ImageConverter();

        foreach ($this->logoUploads as $upload) {
            $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($upload->getRealPath());

            if ($realMime === 'image/svg+xml') {
                $filename = \Illuminate\Support\Str::random(32).'.svg';
                $storagePath = 'media/'.$filename;
                Storage::disk('public')->put($storagePath, $upload->get());
            } else {
                $webpPath = $converter->toWebP($upload->getRealPath());
                $filename = \Illuminate\Support\Str::random(32).'.webp';
                $storagePath = 'media/'.$filename;
                Storage::disk('public')->put($storagePath, file_get_contents($webpPath));
                unlink($webpPath);
            }

            $url = Storage::disk('public')->url($storagePath);

            if ($this->logoTarget === 'footer') {
                $this->footerLogoUrl = $url;
            } elseif ($this->logoTarget === 'favicon') {
                $this->faviconUrl = $url;
            } else {
                $this->logoUrl = $url;
            }
        }

        $this->logoUploads = [];
        $this->isUploadingLogo = false;
    }

    public function openLogoPicker(string $target = 'main'): void
    {
        Gate::authorize('admin-action');
        $this->logoTarget = in_array($target, ['footer', 'favicon']) ? $target : 'main';
        $this->showLogoPicker = true;
    }

    public function pickLogo(int $mediaId): void
    {
        Gate::authorize('admin-action');

        $media = \App\Models\Media::findOrFail($mediaId);

        if ($this->logoTarget === 'footer') {
            $this->footerLogoUrl = $media->path;
        } elseif ($this->logoTarget === 'favicon') {
            $this->faviconUrl = $media->path;
        } else {
            $this->logoUrl = $media->path;
        }

        $this->showLogoPicker = false;
        $this->logoSearch = '';
    }

    public function clearLogo(string $target = 'main'): void
    {
        Gate::authorize('admin-action');

        if ($target === 'footer') {
            $this->footerLogoUrl = '';
        } elseif ($target === 'favicon') {
            $this->faviconUrl = '';
        } else {
            $this->logoUrl = '';
        }
    }

    public function save(): void
    {
        Gate::authorize('admin-action');

        $this->validate([
            'siteName' => ['required', 'string', 'max:100'],
            'siteTagline' => ['nullable', 'string', 'max:200'],
            'siteDescription' => ['nullable', 'string', 'max:2000'],
            'logoUrl' => ['nullable', 'string', 'max:500'],
            'footerLogoUrl' => ['nullable', 'string', 'max:500'],
            'faviconUrl' => ['nullable', 'string', 'max:500'],
            'whatsappNumber' => ['required', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'socialInstagram' => ['nullable', 'string', 'max:100'],
            'socialFacebook' => ['nullable', 'string', 'max:100'],
            'socialTiktok' => ['nullable', 'string', 'max:100'],
            'freeShippingThreshold' => ['required', 'integer', 'min:0'],
        ]);

        Setting::set('site_name', $this->siteName, 'general');
        Setting::set('shop_name', $this->siteName, 'general');
        Setting::set('site_tagline', $this->siteTagline, 'general');
        Setting::set('site_description', $this->siteDescription, 'general');
        Setting::set('logo_url', $this->logoUrl, 'branding');
        Setting::set('footer_logo_url', $this->footerLogoUrl, 'branding');
        Setting::set('favicon_url', $this->faviconUrl, 'branding');
        Setting::set('whatsapp_number', $this->whatsappNumber, 'contact');
        Setting::set('phone', $this->phone, 'contact');
        Setting::set('phone_primary', $this->phone, 'contact');
        Setting::set('social_instagram', $this->socialInstagram, 'social');
        Setting::set('social_facebook', $this->socialFacebook, 'social');
        Setting::set('social_tiktok', $this->socialTiktok, 'social');
        Setting::set('instagram_url', $this->socialInstagram, 'social');
        Setting::set('facebook_url', $this->socialFacebook, 'social');
        Setting::set('tiktok_url', $this->socialTiktok, 'social');
        Setting::set('free_shipping_threshold', $this->freeShippingThreshold, 'livraison');

        $this->successMessage = 'Paramètres boutique enregistrés.';
    }

    #[Computed]
    public function pickerMedia(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return \App\Models\Media::query()
            ->where('type', 'image')
            ->when($this->logoSearch, fn ($q) => $q->where('original_name', 'like', "%{$this->logoSearch}%"))
            ->latest()
            ->paginate(18, pageName: 'logoPage');
    }
}; ?>

<div class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500">
    @if($this->successMessage !== '')
        <div class="fixed top-8 right-8 z-[60] flex items-center gap-3 rounded-2xl bg-emerald-500 text-white px-6 py-4 shadow-2xl shadow-emerald-500/20 animate-in slide-in-from-right-10 duration-500"
             x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <div class="size-6 bg-white/20 rounded-full flex items-center justify-center">
                <flux:icon.check class="size-4" />
            </div>
            <p class="text-sm font-black uppercase tracking-widest">{{ $this->successMessage }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-10">
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                    <flux:icon.building-storefront class="size-5 text-zinc-400" />
                </div>
                <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Identité</h3>
            </div>

            <div class="grid gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Nom de l'enseigne</label>
                    <flux:input wire:model="siteName" variant="filled" :placeholder="'Ex: ' . config('app.name')" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Slogan publicitaire</label>
                    <flux:input wire:model="siteTagline" variant="filled" placeholder="Ex: Votre slogan ici" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Description de la boutique</label>
                    <flux:textarea wire:model="siteDescription" variant="filled" rows="5" placeholder="Présentation de votre boutique…" class="!bg-zinc-50 dark:!bg-zinc-800 font-medium" />
                </div>

                <div class="space-y-2 border-t border-zinc-100 dark:border-zinc-800/50 pt-8 mt-8">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Logo du site</label>
                    <p class="text-[9px] text-zinc-500 font-bold">Format: PNG, JPG, WebP (idéalement fond transparent)</p>

                    @if($this->logoUrl !== '')
                        <div class="relative group rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 bg-black h-32 flex items-center justify-center">
                            @php $logoAsset = Setting::resolveMediaUrl($this->logoUrl); @endphp
                            <img src="{{ $logoAsset }}" alt="Logo" class="max-h-full max-w-full object-contain">
                            <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-4">
                                <flux:button type="button" wire:click="openLogoPicker('main')" size="sm" variant="filled" class="!bg-white !text-zinc-900 !font-black uppercase tracking-widest text-[10px]">
                                    Modifier
                                </flux:button>
                                <flux:button type="button" wire:click="clearLogo('main')" size="sm" variant="danger" class="!bg-rose-500 !font-black uppercase tracking-widest text-[10px]">
                                    Supprimer
                                </flux:button>
                            </div>
                        </div>
                    @else
                        <button type="button"
                                wire:click="openLogoPicker('main')"
                                class="w-full h-32 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl p-8 text-center hover:border-brand-primary/50 hover:bg-brand-primary/5 transition group flex flex-col items-center justify-center gap-3">
                            <div class="size-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 group-hover:bg-brand-primary/10 flex items-center justify-center transition-colors">
                                <flux:icon.plus class="size-6 text-zinc-400 group-hover:text-brand-primary transition-colors" />
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Ajouter un logo</p>
                                <p class="text-xs text-zinc-400">Depuis la médiathèque ou importez un fichier</p>
                            </div>
                        </button>
                    @endif
                </div>

                <div class="space-y-2 border-t border-zinc-100 dark:border-zinc-800/50 pt-8 mt-8">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Favicon</label>
                    <p class="text-[9px] text-zinc-500 font-bold">Icône affichée dans l'onglet du navigateur. Format carré recommandé (ex: 64×64).</p>

                    @if($this->faviconUrl !== '')
                        <div class="relative group rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 h-32 flex items-center justify-center">
                            @php $faviconAsset = Setting::resolveMediaUrl($this->faviconUrl); @endphp
                            <img src="{{ $faviconAsset }}" alt="Favicon" class="max-h-16 max-w-16 object-contain">
                            <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-4">
                                <flux:button type="button" wire:click="openLogoPicker('favicon')" size="sm" variant="filled" class="!bg-white !text-zinc-900 !font-black uppercase tracking-widest text-[10px]">
                                    Modifier
                                </flux:button>
                                <flux:button type="button" wire:click="clearLogo('favicon')" size="sm" variant="danger" class="!bg-rose-500 !font-black uppercase tracking-widest text-[10px]">
                                    Supprimer
                                </flux:button>
                            </div>
                        </div>
                    @else
                        <button type="button"
                                wire:click="openLogoPicker('favicon')"
                                class="w-full h-32 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl p-8 text-center hover:border-brand-primary/50 hover:bg-brand-primary/5 transition group flex flex-col items-center justify-center gap-3">
                            <div class="size-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 group-hover:bg-brand-primary/10 flex items-center justify-center transition-colors">
                                <flux:icon.plus class="size-6 text-zinc-400 group-hover:text-brand-primary transition-colors" />
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Ajouter un favicon</p>
                                <p class="text-xs text-zinc-400">Depuis la médiathèque ou importez un fichier</p>
                            </div>
                        </button>
                    @endif
                </div>

                <div class="space-y-2 border-t border-zinc-100 dark:border-zinc-800/50 pt-8 mt-8">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Logo footer (fond sombre)</label>
                    <p class="text-[9px] text-zinc-500 font-bold">Optionnel. Si vide, le footer reprend le logo principal.</p>

                    @if($this->footerLogoUrl !== '')
                        <div class="relative group rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 bg-black h-32 flex items-center justify-center">
                            @php $footerLogoAsset = Setting::resolveMediaUrl($this->footerLogoUrl); @endphp
                            <img src="{{ $footerLogoAsset }}" alt="Logo footer" class="max-h-full max-w-full object-contain">
                            <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-4">
                                <flux:button type="button" wire:click="openLogoPicker('footer')" size="sm" variant="filled" class="!bg-white !text-zinc-900 !font-black uppercase tracking-widest text-[10px]">
                                    Modifier
                                </flux:button>
                                <flux:button type="button" wire:click="clearLogo('footer')" size="sm" variant="danger" class="!bg-rose-500 !font-black uppercase tracking-widest text-[10px]">
                                    Supprimer
                                </flux:button>
                            </div>
                        </div>
                    @else
                        <button type="button"
                                wire:click="openLogoPicker('footer')"
                                class="w-full h-32 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl p-8 text-center hover:border-brand-primary/50 hover:bg-brand-primary/5 transition group flex flex-col items-center justify-center gap-3">
                            <div class="size-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 group-hover:bg-brand-primary/10 flex items-center justify-center transition-colors">
                                <flux:icon.plus class="size-6 text-zinc-400 group-hover:text-brand-primary transition-colors" />
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Ajouter un logo footer</p>
                                <p class="text-xs text-zinc-400">Variante recommandÃ©e pour fond noir</p>
                            </div>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6 border-t border-zinc-100 dark:border-zinc-800/50 pt-10">
            <div class="flex items-center gap-3">
                <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                    <flux:icon.truck class="size-5 text-zinc-400" />
                </div>
                <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Contact & Ventes</h3>
            </div>

            <div class="grid gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Téléphone affiché</label>
                    <flux:input wire:model="phone" variant="filled" placeholder="Ex: 77 618 23 33" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">WhatsApp Business</label>
                    <flux:input wire:model="whatsappNumber" variant="filled" placeholder="Ex: 221776182333" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                    <p class="text-[9px] text-zinc-500 font-bold uppercase tracking-wider">Format: Code pays + numéro</p>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Franchise de port (FCFA)</label>
                    <flux:input wire:model="freeShippingThreshold" type="number" variant="filled" min="0" placeholder="Ex: 75000" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                    <p class="text-[9px] text-zinc-500 font-bold uppercase tracking-wider">Montant min. pour livraison offerte</p>
                </div>
            </div>
        </div>

        <div class="space-y-6 border-t border-zinc-100 dark:border-zinc-800/50 pt-10">
            <div class="flex items-center gap-3">
                <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                    <flux:icon.arrow-up-tray class="size-5 text-zinc-400" />
                </div>
                <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Réseaux Sociaux</h3>
            </div>

            <div class="grid gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Instagram</label>
                    <flux:input wire:model="socialInstagram" variant="filled" placeholder="https://instagram.com/..." class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Facebook</label>
                    <flux:input wire:model="socialFacebook" variant="filled" placeholder="https://facebook.com/..." class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">TikTok</label>
                    <flux:input wire:model="socialTiktok" variant="filled" placeholder="https://tiktok.com/..." class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                </div>
            </div>
        </div>

        <flux:button wire:click="save" variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px] py-4 w-full rounded-2xl shadow-xl shadow-brand-primary/20">
            Enregistrer
        </flux:button>
    </div>

    {{-- Modal sélection logo --}}
    @if($this->showLogoPicker)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-6"
             x-data
             x-on:keydown.escape.window="$wire.set('showLogoPicker', false)">

            <div class="absolute inset-0 bg-zinc-950/80 backdrop-blur-md transition-opacity"
                 wire:click="$set('showLogoPicker', false)"></div>

            <div class="relative bg-white dark:bg-zinc-900 rounded-[2rem] shadow-2xl w-full max-w-5xl max-h-[85vh] flex flex-col overflow-hidden border border-zinc-200 dark:border-zinc-800 transition-all transform animate-in zoom-in-95 duration-300">

                {{-- Header --}}
                <div class="flex items-center justify-between px-10 py-8 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h2 class="text-2xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Sélectionner un logo</h2>
                        <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold mt-1">Choisissez une image depuis la médiathèque</p>
                    </div>
                    <button type="button" wire:click="$set('showLogoPicker', false)"
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
                                     'logoUploads',
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
                        x-on:click="$refs.logoInput.click()"
                    >
                        <input x-ref="logoInput" type="file" class="hidden"
                               accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                               x-on:change="uploadInBatches($refs.logoInput.files)">

                        @if($this->isUploadingLogo)
                            <div class="flex items-center justify-center gap-4 py-2">
                                <div class="size-5 border-2 border-brand-primary border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Conversion WebP…</span>
                            </div>
                        @else
                            <div class="flex items-center justify-center gap-4 py-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                <flux:icon.cloud-arrow-up class="size-6 text-brand-primary" />
                                <span class="text-sm text-zinc-500 dark:text-zinc-400 font-bold uppercase tracking-widest text-[10px]">Glisser ou cliquer pour <span class="text-brand-primary">importer</span></span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Recherche --}}
                <div class="px-10 pt-6">
                    <flux:input wire:model.live.debounce.300ms="logoSearch" placeholder="Chercher un logo…" icon="magnifying-glass" variant="filled" class="!h-10 !text-xs font-bold" />
                </div>

                {{-- Grille --}}
                <div class="flex-1 overflow-y-auto px-10 pb-8 pt-6">
                    @if($this->pickerMedia->isEmpty())
                        <div class="flex flex-col items-center justify-center py-20 text-zinc-300 dark:text-zinc-700">
                            <flux:icon.photo class="size-20 mb-4 opacity-10" />
                            <p class="text-sm font-black uppercase tracking-widest">Aucun résultat</p>
                            <p class="text-[10px] mt-1 font-bold">Importez votre premier logo pour commencer.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-7 gap-4">
                            @foreach($this->pickerMedia as $media)
                                <button
                                    type="button"
                                    wire:click="pickLogo({{ $media->id }})"
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
</div>
