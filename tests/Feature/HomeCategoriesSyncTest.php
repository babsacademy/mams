<?php

use App\Models\Category;

it('renders homepage categories from active dashboard categories', function () {
    $activeCategoryOne = Category::factory()->create([
        'name' => 'Vestes Tech',
        'slug' => 'vestes-tech',
        'is_active' => true,
        'image_url' => null,
    ]);

    $activeCategoryTwo = Category::factory()->create([
        'name' => 'Sneakers Urbaines',
        'slug' => 'sneakers-urbaines',
        'is_active' => true,
        'image_url' => null,
    ]);

    $inactiveCategory = Category::factory()->create([
        'name' => 'Archive Privée',
        'slug' => 'archive-privee',
        'is_active' => false,
        'image_url' => null,
    ]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee($activeCategoryOne->name)
        ->assertSee($activeCategoryTwo->name)
        ->assertSee(route('catalogue', ['category' => $activeCategoryOne->slug]), false)
        ->assertSee(route('catalogue', ['category' => $activeCategoryTwo->slug]), false)
        ->assertDontSee($inactiveCategory->name);
});
