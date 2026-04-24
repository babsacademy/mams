<?php

use App\Models\Media;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

test('home page renders hero with default settings', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Revele ta')
        ->assertSee('beaute');
});

test('home page hero reflects custom settings from database', function () {
    Setting::set('hero_title_line1', 'Nouveau titre', 'hero');
    Setting::set('hero_title_line2', 'personnalise.', 'hero');
    Setting::set('hero_badge', 'Soldes 2026', 'hero');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Nouveau titre')
        ->assertSee('personnalise.')
        ->assertSee('Soldes 2026');
});

it('normalizes hero image paths on homepage', function (string $storedPath) {
    Setting::set('hero_image_url', $storedPath, 'hero');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('/storage/media/images/hero.webp', false);
})->with([
    'media path without prefix' => 'media/images/hero.webp',
    'media path with storage prefix' => '/storage/media/images/hero.webp',
]);

test('home page keeps external hero image urls', function () {
    $externalUrl = 'https://cdn.example.com/hero.webp';
    Setting::set('hero_image_url', $externalUrl, 'hero');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($externalUrl, false);
});

test('home page applies hero image focus position from settings', function () {
    Setting::set('hero_image_url', 'media/images/hero.webp', 'hero');
    Setting::set('hero_image_position_x', 73, 'hero');
    Setting::set('hero_image_position_y', 18, 'hero');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('object-position: 73% 18%;', false);
});

test('admin can see storefront page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.storefront.index'))
        ->assertOk()
        ->assertSee('Vitrine');
});

test('admin can save hero settings from storefront', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test('admin.storefront.index')
        ->set('heroTitleLine1', 'Cuir noble')
        ->set('heroTitleLine2', 'artisanal.')
        ->set('heroBadge', 'Ete 2026')
        ->set('heroDescription', 'Description de test.')
        ->set('heroCta1Text', 'Voir la boutique')
        ->set('heroCta2Text', 'En savoir plus')
        ->call('saveHero')
        ->assertHasNoErrors();

    expect(Setting::get('hero_title_line1'))->toBe('Cuir noble');
    expect(Setting::get('hero_title_line2'))->toBe('artisanal.');
    expect(Setting::get('hero_badge'))->toBe('Ete 2026');
    expect(Setting::get('hero_cta1_text'))->toBe('Voir la boutique');
});

test('storefront keeps hero fields synchronized after save', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test('admin.storefront.index')
        ->set('heroBadge', 'Drop 2026')
        ->set('heroTitleLine1', 'PRO MAX')
        ->set('heroTitleLine2', 'reinvente.')
        ->set('heroDescription', 'Texte test vitrine.')
        ->set('heroCta1Text', 'Decouvrir')
        ->set('heroCta2Text', 'En savoir plus')
        ->call('saveHero')
        ->assertSet('heroBadge', 'Drop 2026')
        ->assertSet('heroTitleLine1', 'PRO MAX')
        ->assertSet('heroTitleLine2', 'reinvente.')
        ->assertSet('heroDescription', 'Texte test vitrine.')
        ->assertSet('heroCta1Text', 'Decouvrir')
        ->assertSet('heroCta2Text', 'En savoir plus');
});

test('admin can set hero image focus from storefront', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test('admin.storefront.index')
        ->call('setHeroImageFocus', 22, 64)
        ->assertSet('heroImagePositionX', 22)
        ->assertSet('heroImagePositionY', 64);

    expect(Setting::get('hero_image_position_x'))->toBe('22');
    expect(Setting::get('hero_image_position_y'))->toBe('64');
});

test('admin media selection persists hero image immediately', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $media = Media::create([
        'filename' => 'hero-test.webp',
        'original_name' => 'hero-test.webp',
        'path' => 'media/hero-test.webp',
        'disk' => 'public',
        'size' => 1024,
        'width' => 1200,
        'height' => 800,
        'type' => 'image',
    ]);

    Livewire::actingAs($admin)
        ->test('admin.storefront.index')
        ->call('pickMedia', $media->id)
        ->assertSet('heroImageUrl', $media->path);

    expect(Setting::get('hero_image_url'))->toBe($media->path);
});

test('admin clear hero image persists immediately', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Setting::set('hero_image_url', 'media/existing.webp', 'hero');

    Livewire::actingAs($admin)
        ->test('admin.storefront.index')
        ->set('heroImageUrl', 'media/existing.webp')
        ->call('clearImage', 'hero')
        ->assertSet('heroImageUrl', '');

    expect(Setting::get('hero_image_url'))->toBe('');
});

test('hero settings require title lines', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test('admin.storefront.index')
        ->set('heroTitleLine1', '')
        ->set('heroTitleLine2', '')
        ->call('saveHero')
        ->assertHasErrors(['heroTitleLine1', 'heroTitleLine2']);
});

test('non-admin cannot access storefront page', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.storefront.index'))
        ->assertForbidden();
});

test('non-admin cannot access admin settings', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.settings.index'))
        ->assertForbidden();
});
