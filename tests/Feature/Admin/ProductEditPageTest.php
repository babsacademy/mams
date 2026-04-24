<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('admin can open product edit page', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'email_verified_at' => now(),
    ]);

    $product = Product::factory()->create();

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.products.edit', $product));

    $response->assertOk();
});

test('admin can update product length and color guidance fields', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'email_verified_at' => now(),
    ]);

    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create([
        'length_label' => null,
        'color_label' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test('admin.products.create-edit', ['product' => $product])
        ->set('name', $product->name)
        ->set('categoryId', (string) $category->id)
        ->set('price', (string) $product->price)
        ->set('stock', (string) $product->stock)
        ->set('lengthLabel', '28"')
        ->set('colorLabel', 'Noir & Marron')
        ->call('save')
        ->assertHasNoErrors();

    expect($product->fresh()->length_label)->toBe('28"');
    expect($product->fresh()->color_label)->toBe('Noir & Marron');
});
