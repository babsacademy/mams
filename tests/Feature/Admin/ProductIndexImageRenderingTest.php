<?php

use App\Models\Product;
use App\Models\User;

test('product index renders storage image paths without duplicating storage prefix', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'email_verified_at' => now(),
    ]);

    $product = Product::factory()->create([
        'name' => 'Le Signature Noir',
        'image_url' => '/storage/media/images/signature-noir.webp',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.products.index'));

    $response->assertOk();
    $response->assertSee('/storage/media/images/signature-noir.webp');
    $response->assertDontSee('/storage//storage/media/images/signature-noir.webp');
    $response->assertDontSee('/storage/storage/media/images/signature-noir.webp');
});
