<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Perruques Lace', 'image_url' => '/mams-template/assets/images/hero.png'],
            ['name' => 'Bundles & Tissages', 'image_url' => '/mams-template/assets/images/bifaft.png'],
            ['name' => 'Closures & Frontals', 'image_url' => '/mams-template/assets/images/pr.png'],
            ['name' => 'Headband Wigs', 'image_url' => '/mams-template/assets/images/prod.png'],
            ['name' => 'Soins Capillaires', 'image_url' => '/mams-template/assets/images/prod.png'],
            ['name' => 'Accessoires', 'image_url' => '/mams-template/assets/images/bifaft.png'],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'image_url' => $data['image_url'],
                    'is_active' => true,
                ]
            );
        }
    }
}
