<?php

use App\Models\User;

test('admin can open settings page', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'email_verified_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.settings.index'));

    $response
        ->assertOk()
        ->assertSee('Configuration');
});
