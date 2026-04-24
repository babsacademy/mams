<?php

use App\Models\Setting;

it('uses dedicated footer logo when configured', function () {
    $mainLogoUrl = 'https://cdn.example.com/logo-main.webp';
    $footerLogoUrl = 'https://cdn.example.com/logo-footer.webp';

    Setting::set('logo_url', $mainLogoUrl, 'branding');
    Setting::set('footer_logo_url', $footerLogoUrl, 'branding');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($mainLogoUrl, false)
        ->assertSee($footerLogoUrl, false);
});

it('falls back to main logo in footer when footer logo is empty', function () {
    $mainLogoUrl = 'https://cdn.example.com/logo-main-only.webp';

    Setting::set('logo_url', $mainLogoUrl, 'branding');
    Setting::set('footer_logo_url', '', 'branding');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($mainLogoUrl, false);
});
