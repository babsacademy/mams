<?php
use App\Models\Media;
use App\Models\Setting;
use App\Services\ImageConverter;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Vitrine')] #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads, WithPagination;

    // Hero
    public string $heroImageUrl   = '';
    public string $heroBadge      = '';
    public string $heroTitleLine1 = '';
    public string $heroTitleLine2 = '';
    public string $heroDescription = '';
    public string $heroCta1Text   = '';
    public string $heroCta2Text   = '';
    public int $heroImagePositionX = 50;
    public int $heroImagePositionY = 0;

    // Section Éditoriale (deux images + texte)
    public string $editorialImageLeft  = '';
    public string $editorialImageRight = '';
    public string $editorialBadge      = '';
    public string $editorialTitle      = '';
    public string $editorialText       = '';
    public string $editorialLinkText   = '';

    // Section Savoir-faire
    public string $craftImage    = '';
    public string $craftTitle    = '';
    public string $craftText     = '';
    public string $craftBadge1   = '';
    public string $craftBadge2   = '';

    public string $successMessage = '';

    // Médiathèque
    public bool $showMediaPicker   = false;
    public string $mediaPickerTarget = 'hero'; // 'hero', 'editorial_left', 'editorial_right'
    public string $mediaSearch     = '';
    public bool $isUploadingMedia  = false;
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $mediaUploads     = [];

    public function mount(): void
    {
        $this->loadHeroSettings();

        $this->editorialImageLeft  = Setting::get('editorial_image_left', '');
        $this->editorialImageRight = Setting::get('editorial_image_right', '');
        $this->editorialBadge      = Setting::get('editorial_badge', 'Collections');
        $this->editorialTitle      = Setting::get('editorial_title', 'Nos produits, votre beauté');
        $this->editorialText       = Setting::get('editorial_text', 'Découvrez notre sélection premium de cheveux, perruques et accessoires beauté pensée pour sublimer chaque style.');
        $this->editorialLinkText   = Setting::get('editorial_link_text', 'Explorer la boutique');

        $this->craftImage  = Setting::get('craft_image', '');
        $this->craftTitle  = Setting::get('craft_title', "L'Art du Cuir Dakarois");
        $this->craftText   = Setting::get('craft_text', "Chez " . config('app.name') . ", chaque création est pensée avec passion et expertise.");
        $this->craftBadge1 = Setting::get('craft_badge_line1', 'Fait main à Dakar');
        $this->craftBadge2 = Setting::get('craft_badge_line2', 'avec passion depuis 2015');
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

    public function openMediaPicker(string $target = 'hero'): void
    {
        $this->mediaPickerTarget = $target;
        $this->showMediaPicker   = true;
    }

    public function pickMedia(int $mediaId): void
    {
        $media = Media::findOrFail($mediaId);

        match ($this->mediaPickerTarget) {
            'craft'            => $this->craftImage = $media->path,
            'editorial_left'   => $this->editorialImageLeft = $media->path,
            'editorial_right'  => $this->editorialImageRight = $media->path,
            default            => $this->heroImageUrl = $media->path,
        };

        match ($this->mediaPickerTarget) {
            'craft'            => Setting::set('craft_image', $this->craftImage, 'vitrine'),
            'editorial_left'   => Setting::set('editorial_image_left', $this->editorialImageLeft, 'vitrine'),
            'editorial_right'  => Setting::set('editorial_image_right', $this->editorialImageRight, 'vitrine'),
            default            => Setting::set('hero_image_url', $this->heroImageUrl, 'hero'),
        };

        $this->showMediaPicker = false;
        $this->mediaSearch     = '';
    }

    public function clearImage(string $target = 'hero'): void
    {
        match ($target) {
            'craft'           => [$this->craftImage = '', Setting::set('craft_image', '', 'vitrine')],
            'editorial_left'  => [$this->editorialImageLeft = '', Setting::set('editorial_image_left', '', 'vitrine')],
            'editorial_right' => [$this->editorialImageRight = '', Setting::set('editorial_image_right', '', 'vitrine')],
            default           => [$this->heroImageUrl = '', Setting::set('hero_image_url', '', 'hero')],
        };
    }

    public function saveEditorial(): void
    {
        $this->validate([
            'editorialBadge'    => ['nullable', 'string', 'max:60'],
            'editorialTitle'    => ['nullable', 'string', 'max:120'],
            'editorialText'     => ['nullable', 'string', 'max:500'],
            'editorialLinkText' => ['nullable', 'string', 'max:60'],
        ]);

        Setting::set('editorial_image_left', $this->editorialImageLeft, 'vitrine');
        Setting::set('editorial_image_right', $this->editorialImageRight, 'vitrine');
        Setting::set('editorial_badge', $this->editorialBadge, 'vitrine');
        Setting::set('editorial_title', $this->editorialTitle, 'vitrine');
        Setting::set('editorial_text', $this->editorialText, 'vitrine');
        Setting::set('editorial_link_text', $this->editorialLinkText, 'vitrine');

        $this->successMessage = 'Section éditoriale mise à jour.';
    }

    public function updatedHeroImageUrl(): void
    {
        Setting::set('hero_image_url', $this->heroImageUrl, 'hero');
    }

    public function updatedHeroImagePositionX(int $value): void
    {
        $this->heroImagePositionX = max(0, min(100, $value));
        Setting::set('hero_image_position_x', $this->heroImagePositionX, 'hero');
    }

    public function updatedHeroImagePositionY(int $value): void
    {
        $this->heroImagePositionY = max(0, min(100, $value));
        Setting::set('hero_image_position_y', $this->heroImagePositionY, 'hero');
    }

    public function setHeroImageFocus(int $x, int $y): void
    {
        $this->heroImagePositionX = max(0, min(100, $x));
        $this->heroImagePositionY = max(0, min(100, $y));
        Setting::set('hero_image_position_x', $this->heroImagePositionX, 'hero');
        Setting::set('hero_image_position_y', $this->heroImagePositionY, 'hero');
    }

    public function saveHero(): void
    {
        $this->validate([
            'heroImageUrl'    => ['nullable', 'string', 'max:500'],
            'heroBadge'       => ['nullable', 'string', 'max:60'],
            'heroTitleLine1'  => ['required', 'string', 'max:80'],
            'heroTitleLine2'  => ['required', 'string', 'max:80'],
            'heroDescription' => ['nullable', 'string', 'max:400'],
            'heroCta1Text'    => ['nullable', 'string', 'max:60'],
            'heroCta2Text'    => ['nullable', 'string', 'max:60'],
            'heroImagePositionX' => ['required', 'integer', 'between:0,100'],
            'heroImagePositionY' => ['required', 'integer', 'between:0,100'],
        ]);

        Setting::set('hero_image_url', $this->heroImageUrl, 'hero');
        Setting::set('hero_badge', $this->heroBadge, 'hero');
        Setting::set('hero_title_line1', $this->heroTitleLine1, 'hero');
        Setting::set('hero_title_line2', $this->heroTitleLine2, 'hero');
        Setting::set('hero_description', $this->heroDescription, 'hero');
        Setting::set('hero_cta1_text', $this->heroCta1Text, 'hero');
        Setting::set('hero_cta2_text', $this->heroCta2Text, 'hero');
        Setting::set('hero_image_position_x', $this->heroImagePositionX, 'hero');
        Setting::set('hero_image_position_y', $this->heroImagePositionY, 'hero');

        $this->successMessage = 'Vitrine mise à jour avec succès.';
        $this->loadHeroSettings();
    }

    public function saveCraft(): void
    {
        $this->validate([
            'craftImage'  => ['nullable', 'string', 'max:500'],
            'craftTitle'  => ['nullable', 'string', 'max:100'],
            'craftText'   => ['nullable', 'string', 'max:600'],
            'craftBadge1' => ['nullable', 'string', 'max:60'],
            'craftBadge2' => ['nullable', 'string', 'max:60'],
        ]);

        Setting::set('craft_image', $this->craftImage, 'vitrine');
        Setting::set('craft_title', $this->craftTitle, 'vitrine');
        Setting::set('craft_text', $this->craftText, 'vitrine');
        Setting::set('craft_badge_line1', $this->craftBadge1, 'vitrine');
        Setting::set('craft_badge_line2', $this->craftBadge2, 'vitrine');

        $this->successMessage = 'Section Savoir-faire mise à jour.';
    }

    protected function loadHeroSettings(): void
    {
        $this->heroImageUrl = Setting::get('hero_image_url', 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?q=80&w=2000');
        $this->heroBadge = Setting::get('hero_badge', 'Collection 2024');
        $this->heroTitleLine1 = Setting::get('hero_title_line1', 'Le cuir');
        $this->heroTitleLine2 = Setting::get('hero_title_line2', 'réinventé.');
        $this->heroDescription = Setting::get('hero_description', "Maroquinerie d'exception façonnée à la main dans notre atelier de Dakar. Chaque pièce incarne le raffinement et la durabilité du cuir premium sénégalais.");
        $this->heroCta1Text = Setting::get('hero_cta1_text', 'Découvrir la boutique');
        $this->heroCta2Text = Setting::get('hero_cta2_text', 'Notre savoir-faire');
        $this->heroImagePositionX = max(0, min(100, (int) Setting::get('hero_image_position_x', 50)));
        $this->heroImagePositionY = max(0, min(100, (int) Setting::get('hero_image_position_y', 0)));
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

<div class="space-y-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Vitrine</h1>
            <p class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-widest font-bold mt-1">Personnalisez l'apparence de votre page d'accueil.</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-2 text-[10px] font-black text-zinc-400 uppercase tracking-widest hover:text-brand-primary transition-colors">
            <flux:icon.eye class="size-4" />
            Voir le résultat en direct
        </a>
    </div>

    @if($successMessage)
        <div class="fixed top-8 right-8 z-[60] flex items-center gap-3 rounded-2xl bg-emerald-500 text-white px-6 py-4 shadow-2xl shadow-emerald-500/20 animate-in slide-in-from-right-10 duration-500"
             x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <div class="size-6 bg-white/20 rounded-full flex items-center justify-center">
                <flux:icon.check class="size-4" />
            </div>
            <p class="text-sm font-black uppercase tracking-widest">{{ $successMessage }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-10 items-start">
        <div class="space-y-8">
            <div class="rounded-[2.5rem] overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-2xl relative bg-black">
                <p class="absolute top-3 right-3 z-20 bg-black/60 backdrop-blur-md border border-white/10 text-white/70 text-[9px] uppercase tracking-[0.2em] px-3 py-1 rounded-full font-black">Aperçu</p>
                <section class="relative h-[28rem] w-full bg-black overflow-hidden">
                    @if($heroImageUrl)
                        @php $heroImageAsset = Setting::resolveMediaUrl($heroImageUrl); @endphp
                        <img src="{{ $heroImageAsset }}" alt="Aperçu hero"
                             class="absolute inset-0 h-full w-full object-cover object-center"
                             style="object-position: {{ $heroImagePositionX }}% {{ $heroImagePositionY }}%;">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/55 to-black/70"></div>
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_45%,rgba(201,169,110,0.22),transparent_52%)]"></div>

                    <div class="relative h-full flex items-center px-8 lg:px-12">
                        <div class="max-w-sm py-10">
                            @if($heroBadge)
                                <p class="uppercase tracking-[0.2em] text-[10px] font-semibold text-[#c9a96e] mb-4">{{ $heroBadge }}</p>
                            @endif
                            <h2 class="font-display text-4xl leading-[0.95] text-white">
                                {{ $heroTitleLine1 }}<br>{{ $heroTitleLine2 }}
                            </h2>
                            @if($heroDescription)
                                <p class="mt-4 text-[#f5f0e8] text-sm line-clamp-2 opacity-90">{{ $heroDescription }}</p>
                            @endif
                            <div class="mt-6 flex flex-wrap gap-3">
                                <div class="inline-flex h-9 items-center justify-center rounded-full bg-[#c9a96e] px-5 text-[10px] font-semibold uppercase tracking-[0.16em] text-black">
                                    {{ $heroCta1Text ?: 'Découvrir la collection' }}
                                </div>
                                <div class="inline-flex h-9 items-center justify-center rounded-full border border-white/20 px-5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white">
                                    {{ $heroCta2Text ?: 'Notre savoir-faire' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        {{-- Aperçu live --}}
        @if(false)
            <div class="space-y-8 hidden">
            <div class="hidden rounded-[2.5rem] overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-2xl relative h-[28rem] bg-zinc-900 group">
                @if($heroImageUrl)
                    @php $heroImageAsset = Setting::resolveMediaUrl($heroImageUrl); @endphp
                    <img src="{{ $heroImageAsset }}" alt="Aperçu hero" class="w-full h-full object-cover opacity-60 transition-transform duration-1000 group-hover:scale-110">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-12">
                    @if($heroBadge)
                        <div class="w-fit px-3 py-1 bg-brand-primary/20 backdrop-blur-md rounded-full border border-brand-primary/30 mb-4 animate-in fade-in slide-in-from-bottom-4">
                            <span class="text-brand-primary text-[9px] uppercase tracking-[0.4em] font-black">{{ $heroBadge }}</span>
                        </div>
                    @endif
                    <h2 class="text-white font-black uppercase leading-[1.1] text-5xl tracking-tighter mb-4">
                        {{ $heroTitleLine1 }}<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-primary via-rose-300 to-white">{{ $heroTitleLine2 }}</span>
                    </h2>
                    @if($heroDescription)
                        <p class="text-white/60 text-sm max-w-sm line-clamp-3 leading-relaxed font-medium">{{ $heroDescription }}</p>
                    @endif

                    <div class="flex gap-4 mt-8">
                        <div class="h-10 px-6 bg-brand-primary rounded-xl flex items-center justify-center text-[10px] font-black text-white uppercase tracking-widest">{{ $heroCta1Text ?: 'Bouton 1' }}</div>
                        <div class="h-10 px-6 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl flex items-center justify-center text-[10px] font-black text-white uppercase tracking-widest">{{ $heroCta2Text ?: 'Bouton 2' }}</div>
                    </div>
                </div>
                <div class="absolute top-8 right-8">
                    <span class="bg-black/60 backdrop-blur-md border border-white/10 text-white/80 text-[10px] uppercase tracking-[0.2em] px-4 py-2 rounded-2xl font-black">Aperçu en temps réel</span>
                </div>
            </div>
        </div>

        @endif

        {{-- Formulaire --}}
        <div class="space-y-8">
            <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-8">
                <div class="flex items-center gap-3">
                    <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                        <flux:icon.photo class="size-5 text-zinc-400" />
                    </div>
                    <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Visuel Principal</h3>
                </div>

                {{-- Image picker --}}
                @if(false)
                    <div class="space-y-3 hidden">
                    @if($heroImageUrl)
                        <div class="relative group rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 aspect-video bg-zinc-50 dark:bg-zinc-900">
                            @php $heroImageAsset = Setting::resolveMediaUrl($heroImageUrl); @endphp
                            <img src="{{ $heroImageAsset }}" alt="Aperçu" class="size-full object-cover">
                            <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-4">
                                <flux:button type="button" wire:click="openMediaPicker('hero')" size="sm" variant="filled" class="!bg-white !text-zinc-900 !font-black uppercase tracking-widest text-[10px]">
                                    Changer
                                </flux:button>
                                <flux:button type="button" wire:click="clearImage('hero')" size="sm" variant="danger" class="!bg-rose-500 !font-black uppercase tracking-widest text-[10px]">
                                    Supprimer
                                </flux:button>
                            </div>
                        </div>
                    @else
                        <button type="button"
                                wire:click="openMediaPicker('hero')"
                                class="w-full h-48 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl text-center hover:border-brand-primary/50 hover:bg-brand-primary/5 transition group flex flex-col items-center justify-center gap-4">
                            <div class="size-14 rounded-2xl bg-zinc-100 dark:bg-zinc-800 group-hover:bg-brand-primary/10 flex items-center justify-center transition-colors">
                                <flux:icon.photo class="size-7 text-zinc-400 group-hover:text-brand-primary transition-colors" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Choisir depuis la médiathèque</p>
                                <p class="text-xs text-zinc-400">Ou collez une URL ci-dessous</p>
                            </div>
                        </button>
                    @endif

                    {{-- URL fallback --}}
                    <flux:input wire:model.blur="heroImageUrl" variant="filled" placeholder="Ou collez une URL externe…" class="!bg-zinc-50 dark:!bg-zinc-800 !h-10 text-xs font-bold" />
                    </div>
                @endif

                <div class="space-y-4">
                    @if($heroImageUrl)
                        @php $heroImageAsset = Setting::resolveMediaUrl($heroImageUrl); @endphp
                        <div
                            x-data="{
                                focusX: $wire.entangle('heroImagePositionX'),
                                focusY: $wire.entangle('heroImagePositionY'),
                                dragging: false,
                                updateFromEvent(event) {
                                    const rect = this.$refs.focusArea.getBoundingClientRect();
                                    const x = ((event.clientX - rect.left) / rect.width) * 100;
                                    const y = ((event.clientY - rect.top) / rect.height) * 100;
                                    this.focusX = Math.max(0, Math.min(100, Math.round(x)));
                                    this.focusY = Math.max(0, Math.min(100, Math.round(y)));
                                },
                                startDrag(event) {
                                    this.dragging = true;
                                    this.updateFromEvent(event);
                                },
                                onMove(event) {
                                    if (!this.dragging) {
                                        return;
                                    }

                                    this.updateFromEvent(event);
                                },
                                endDrag() {
                                    if (!this.dragging) {
                                        return;
                                    }

                                    this.dragging = false;
                                    $wire.setHeroImageFocus(this.focusX, this.focusY);
                                },
                            }"
                            x-on:pointermove.window="onMove($event)"
                            x-on:pointerup.window="endDrag()"
                            x-on:pointercancel.window="endDrag()"
                            class="space-y-3"
                        >
                            <div x-ref="focusArea"
                                 class="relative rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 aspect-video bg-zinc-50 dark:bg-zinc-900 cursor-crosshair select-none"
                                 x-on:pointerdown.prevent="startDrag($event)">
                                <img src="{{ $heroImageAsset }}" alt="Aperçu"
                                     class="size-full object-cover pointer-events-none"
                                     :style="`object-position: ${focusX}% ${focusY}%`">

                                <div class="absolute inset-0 pointer-events-none bg-gradient-to-t from-black/50 via-black/10 to-transparent"></div>

                                <div class="absolute size-7 rounded-full border-2 border-white shadow-lg bg-brand-primary/70 pointer-events-none -translate-x-1/2 -translate-y-1/2"
                                     :style="`left: ${focusX}%; top: ${focusY}%`"></div>

                                <div class="absolute inset-x-0 bottom-0 p-3 flex items-center justify-between gap-2 bg-black/45 backdrop-blur-sm">
                                    <p class="text-[10px] text-white/85 uppercase tracking-widest font-black">
                                        Cliquer-glisser pour cadrer l'image
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <flux:button type="button" wire:click="openMediaPicker('hero')" size="sm" variant="filled" class="!bg-white !text-zinc-900 !font-black uppercase tracking-widest text-[10px]">
                                            Changer
                                        </flux:button>
                                        <flux:button type="button" wire:click="clearImage('hero')" size="sm" variant="danger" class="!bg-rose-500 !font-black uppercase tracking-widest text-[10px]">
                                            Supprimer
                                        </flux:button>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Horizontal</label>
                                        <span class="text-[10px] font-black text-zinc-500" x-text="`${focusX}%`"></span>
                                    </div>
                                    <input type="range" min="0" max="100" step="1" x-model.number="focusX"
                                           x-on:change="$wire.setHeroImageFocus(focusX, focusY)"
                                           class="w-full accent-brand-primary">
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Vertical</label>
                                        <span class="text-[10px] font-black text-zinc-500" x-text="`${focusY}%`"></span>
                                    </div>
                                    <input type="range" min="0" max="100" step="1" x-model.number="focusY"
                                           x-on:change="$wire.setHeroImageFocus(focusX, focusY)"
                                           class="w-full accent-brand-primary">
                                </div>
                            </div>
                        </div>
                    @else
                        <button type="button"
                                wire:click="openMediaPicker('hero')"
                                class="w-full h-48 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl text-center hover:border-brand-primary/50 hover:bg-brand-primary/5 transition group flex flex-col items-center justify-center gap-4">
                            <div class="size-14 rounded-2xl bg-zinc-100 dark:bg-zinc-800 group-hover:bg-brand-primary/10 flex items-center justify-center transition-colors">
                                <flux:icon.photo class="size-7 text-zinc-400 group-hover:text-brand-primary transition-colors" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Choisir depuis la médiathèque</p>
                                <p class="text-xs text-zinc-400">Ou collez une URL ci-dessous</p>
                            </div>
                        </button>
                    @endif

                    <flux:input wire:model.live="heroImageUrl" variant="filled" placeholder="Ou collez une URL externe…" class="!bg-zinc-50 dark:!bg-zinc-800 !h-10 text-xs font-bold" />
                </div>

                <div class="space-y-4 border-t border-zinc-100 dark:border-zinc-800/50 pt-8">
                    <div class="flex items-center gap-3">
                        <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                            <flux:icon.pencil-square class="size-5 text-zinc-400" />
                        </div>
                        <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Rédactionnel</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Badge de mise en avant</label>
                            <flux:input wire:model.blur="heroBadge" variant="filled" placeholder="Ex: Collection 2024" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Titre - Ligne 1</label>
                            <flux:input wire:model.blur="heroTitleLine1" variant="filled" placeholder="Ex: Le luxe" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Titre - Ligne 2 <span class="text-brand-primary">(Couleur)</span></label>
                            <flux:input wire:model.blur="heroTitleLine2" variant="filled" placeholder="Ex: à portée" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Description d'introduction</label>
                            <flux:textarea wire:model.blur="heroDescription" variant="filled" rows="4" placeholder="Évocation de l'univers de votre marque..." class="!bg-zinc-50 dark:!bg-zinc-800 font-bold" />
                        </div>
                    </div>
                </div>

                <div class="space-y-4 border-t border-zinc-100 dark:border-zinc-800/50 pt-8">
                    <div class="flex items-center gap-3">
                        <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                            <flux:icon.cursor-arrow-rays class="size-5 text-zinc-400" />
                        </div>
                        <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Actions</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Texte Bouton 1</label>
                            <flux:input wire:model.blur="heroCta1Text" variant="filled" placeholder="Ex: Découvrir" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Texte Bouton 2</label>
                            <flux:input wire:model.blur="heroCta2Text" variant="filled" placeholder="Ex: Concept" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <flux:button wire:click="saveHero" variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px] py-4 w-full rounded-2xl shadow-xl shadow-brand-primary/20">
                        Sauvegarder la vitrine
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Section Éditoriale --}}
    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-8">
        <div class="flex items-center gap-3">
            <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                <flux:icon.squares-2x2 class="size-5 text-zinc-400" />
            </div>
            <div>
                <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Section Éditoriale</h3>
                <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mt-0.5">Les deux images + texte sous les nouveautés</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Image gauche --}}
            <div class="space-y-3">
                <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Grande image (gauche)</label>
                @php $editorialLeftUrl = Setting::resolveMediaUrl($editorialImageLeft) ?? asset('mams-template/assets/images/prod.png'); @endphp
                <div class="relative group rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 h-52 bg-zinc-50 dark:bg-zinc-900">
                    <img src="{{ $editorialLeftUrl }}" alt="Image gauche" class="size-full object-cover">
                    <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-3">
                        <flux:button type="button" wire:click="openMediaPicker('editorial_left')" size="sm" variant="filled" class="!bg-white !text-zinc-900 !font-black uppercase tracking-widest text-[10px]">Changer</flux:button>
                        <flux:button type="button" wire:click="clearImage('editorial_left')" size="sm" variant="danger" class="!bg-rose-500 !font-black uppercase tracking-widest text-[10px]">Retirer</flux:button>
                    </div>
                </div>
            </div>

            {{-- Image droite --}}
            <div class="space-y-3">
                <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Petite image (droite)</label>
                @php $editorialRightUrl = Setting::resolveMediaUrl($editorialImageRight) ?? asset('mams-template/assets/images/pr.png'); @endphp
                <div class="relative group rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 h-52 bg-zinc-50 dark:bg-zinc-900">
                    <img src="{{ $editorialRightUrl }}" alt="Image droite" class="size-full object-cover">
                    <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-3">
                        <flux:button type="button" wire:click="openMediaPicker('editorial_right')" size="sm" variant="filled" class="!bg-white !text-zinc-900 !font-black uppercase tracking-widest text-[10px]">Changer</flux:button>
                        <flux:button type="button" wire:click="clearImage('editorial_right')" size="sm" variant="danger" class="!bg-rose-500 !font-black uppercase tracking-widest text-[10px]">Retirer</flux:button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Badge</label>
                <flux:input wire:model.blur="editorialBadge" variant="filled" placeholder="Ex: Collections" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Texte du lien</label>
                <flux:input wire:model.blur="editorialLinkText" variant="filled" placeholder="Ex: Explorer la boutique" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
            </div>
            <div class="md:col-span-2 space-y-2">
                <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Titre</label>
                <flux:input wire:model.blur="editorialTitle" variant="filled" placeholder="Ex: Nos produits, votre beauté" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
            </div>
            <div class="md:col-span-2 space-y-2">
                <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Description</label>
                <flux:textarea wire:model.blur="editorialText" variant="filled" rows="3" placeholder="Description de cette section..." class="!bg-zinc-50 dark:!bg-zinc-800 font-bold" />
            </div>
        </div>

        <div class="pt-2">
            <flux:button wire:click="saveEditorial" variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px] py-4 px-10 rounded-2xl shadow-lg shadow-brand-primary/20">
                Sauvegarder
            </flux:button>
        </div>
    </div>

    @if(false)
    {{-- Section Savoir-faire --}}
    <div class="bg-white dark:bg-zinc-900/50 backdrop-blur-sm rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-sm p-10 space-y-8">
        <div class="flex items-center gap-3">
            <div class="size-10 bg-zinc-50 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                <flux:icon.sparkles class="size-5 text-zinc-400" />
            </div>
            <div>
                <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-[0.2em]">Section Savoir-faire</h3>
                <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mt-0.5">"L'Art du Cuir Dakarois" — page d'accueil</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Image --}}
            <div class="space-y-3">
                <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Photo</label>
                @php $craftImageUrl = Setting::resolveMediaUrl($craftImage); @endphp
                @if($craftImageUrl)
                    <div class="relative group rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 h-48">
                        <img src="{{ $craftImageUrl }}" alt="Aperçu savoir-faire" class="size-full object-cover">
                        <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-3">
                            <flux:button type="button" wire:click="openMediaPicker('craft')" size="sm" variant="filled" class="!bg-white !text-zinc-900 !font-black uppercase tracking-widest text-[10px]">Changer</flux:button>
                            <flux:button type="button" wire:click="clearImage('craft')" size="sm" variant="danger" class="!bg-rose-500 !font-black uppercase tracking-widest text-[10px]">Retirer</flux:button>
                        </div>
                    </div>
                @else
                    <button type="button" wire:click="openMediaPicker('craft')"
                        class="w-full h-48 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl hover:border-brand-primary/50 hover:bg-brand-primary/5 transition group flex flex-col items-center justify-center gap-3">
                        <flux:icon.photo class="size-7 text-zinc-300 group-hover:text-brand-primary transition-colors" />
                        <p class="text-xs font-black text-zinc-400 group-hover:text-brand-primary uppercase tracking-widest transition-colors">Choisir depuis la médiathèque</p>
                    </button>
                @endif
            </div>

            {{-- Textes --}}
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Titre</label>
                    <flux:input wire:model="craftTitle" variant="filled" placeholder="Ex: L'Art du Cuir Dakarois" class="!bg-zinc-50 dark:!bg-zinc-800 !h-12 font-bold" />
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Texte de présentation</label>
                    <flux:textarea wire:model="craftText" variant="filled" rows="3" placeholder="Description de votre savoir-faire…" class="!bg-zinc-50 dark:!bg-zinc-800 font-medium" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Badge ligne 1</label>
                        <flux:input wire:model="craftBadge1" variant="filled" placeholder="Fait main à Dakar" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Badge ligne 2</label>
                        <flux:input wire:model="craftBadge2" variant="filled" placeholder="depuis 2015" class="!bg-zinc-50 dark:!bg-zinc-800 !h-11 font-bold" />
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-2">
            <flux:button wire:click="saveCraft" variant="primary" class="!bg-brand-primary border-none font-black uppercase tracking-widest text-[10px] py-4 px-10 rounded-2xl shadow-lg shadow-brand-primary/20">
                Sauvegarder
            </flux:button>
        </div>
    </div>

    @endif

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
                                <span class="text-sm text-zinc-500 dark:text-zinc-400 font-bold uppercase tracking-widest text-[10px]">Glisser ou cliquer pour <span class="text-brand-primary">importer</span></span>
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
</div>
