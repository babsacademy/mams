<?php

use App\Models\Setting;

test('contact page renders whatsapp number from dashboard settings', function () {
    Setting::set('whatsapp_number', '221771112233', 'contact');

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('221771112233');
});

test('contact page renders phone from dashboard settings', function () {
    Setting::set('phone_primary', '776182333', 'contact');

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('776182333');
});
