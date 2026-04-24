<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SettingsSeeder;

test('mams seeders populate contextual storefront data', function () {
    $this->seed([
        AdminSeeder::class,
        CategorySeeder::class,
        ProductSeeder::class,
        OrderSeeder::class,
        SettingsSeeder::class,
    ]);

    expect(Setting::get('shop_name'))->toBe('Mams Store World');
    expect(Setting::get('hero_title_line1'))->toBe('Revele ta');

    expect(Category::query()->where('slug', 'perruques-lace')->exists())->toBeTrue();
    expect(Product::query()->where('slug', 'lace-frontal-body-wave-24')->exists())->toBeTrue();
});
