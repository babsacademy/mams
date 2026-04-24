<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->get()->keyBy('slug');

        $products = [
            [
                'category' => 'perruques-lace',
                'name' => 'Lace Frontal Body Wave 24"',
                'length_label' => '24"',
                'color_label' => 'Noir naturel',
                'description' => 'Perruque lace premium effet body wave avec volume naturel, densite confortable et finition pre-plucked. Pensee pour une clientele evenementielle et quotidienne.',
                'price' => 185000,
                'original_price' => 210000,
                'stock' => 7,
                'image_url' => '/mams-template/assets/images/hero.png',
                'badge' => 'Best seller',
                'is_featured' => true,
                'is_new' => true,
            ],
            [
                'category' => 'perruques-lace',
                'name' => 'HD Lace Straight Bob 12"',
                'length_label' => '12"',
                'color_label' => 'Noir',
                'description' => 'Bob droit HD lace avec rendu propre et leger. Ideal pour un look chic, discret et facile a coiffer.',
                'price' => 95000,
                'original_price' => null,
                'stock' => 10,
                'image_url' => '/mams-template/assets/images/pr.png',
                'badge' => 'Nouveau',
                'is_featured' => true,
                'is_new' => true,
            ],
            [
                'category' => 'bundles-tissages',
                'name' => '3 Bundles Deep Wave 18-20-22"',
                'length_label' => '18-20-22"',
                'color_label' => 'Noir naturel',
                'description' => 'Pack de trois bundles deep wave, fibres humaines de qualite premium avec bonne tenue de boucle et beau volume.',
                'price' => 120000,
                'original_price' => 138000,
                'stock' => 14,
                'image_url' => '/mams-template/assets/images/bifaft.png',
                'badge' => 'Promo',
                'is_featured' => true,
                'is_new' => false,
            ],
            [
                'category' => 'closures-frontals',
                'name' => 'HD Closure 5x5 Natural Curly',
                'color_label' => 'Noir naturel',
                'description' => 'Closure 5x5 HD pour un finish naturel, adaptee aux poses premium et a la personnalisation salon.',
                'price' => 68000,
                'original_price' => null,
                'stock' => 9,
                'image_url' => '/mams-template/assets/images/prod.png',
                'badge' => null,
                'is_featured' => false,
                'is_new' => true,
            ],
            [
                'category' => 'headband-wigs',
                'name' => 'Headband Wig Yaki 20"',
                'length_label' => '20"',
                'color_label' => 'Noir',
                'description' => 'Headband wig texture yaki, pose rapide sans colle, parfaite pour le quotidien ou les depannages express.',
                'price' => 78000,
                'original_price' => null,
                'stock' => 11,
                'image_url' => '/mams-template/assets/images/hero.png',
                'badge' => 'Rapide',
                'is_featured' => false,
                'is_new' => true,
            ],
            [
                'category' => 'soins-capillaires',
                'name' => 'Kit Entretien Lace & Bundles',
                'description' => 'Kit compose de shampoing doux, serum brillance et spray demelant pour prolonger la duree de vie des perruques et bundles.',
                'price' => 24000,
                'original_price' => null,
                'stock' => 20,
                'image_url' => '/mams-template/assets/images/pr.png',
                'badge' => null,
                'is_featured' => false,
                'is_new' => false,
            ],
            [
                'category' => 'accessoires',
                'name' => 'Elastic Band + Wig Cap Duo',
                'description' => 'Accessoires essentiels pour une pose plus propre et confortable: bande elastique ajustable et wig cap en lot.',
                'price' => 8000,
                'original_price' => null,
                'stock' => 35,
                'image_url' => '/mams-template/assets/images/bifaft.png',
                'badge' => null,
                'is_featured' => false,
                'is_new' => false,
            ],
            [
                'category' => 'perruques-lace',
                'name' => 'Water Wave 26" Signature',
                'length_label' => '26"',
                'color_label' => 'Noir intense',
                'description' => 'Modele long water wave pour un effet glamour, forte presence visuelle et rendu premium en shooting comme en ceremonie.',
                'price' => 225000,
                'original_price' => 245000,
                'stock' => 5,
                'image_url' => '/mams-template/assets/images/hero.png',
                'badge' => 'Signature',
                'is_featured' => true,
                'is_new' => true,
            ],
            [
                'category' => 'bundles-tissages',
                'name' => 'Raw Straight Luxury Bundle',
                'length_label' => '28"',
                'color_label' => 'Noir naturel',
                'description' => 'Bundle raw straight premium pour clientele recherchant la longueur, la tenue et une belle fluidite au coiffage.',
                'price' => 52000,
                'original_price' => null,
                'stock' => 16,
                'image_url' => '/mams-template/assets/images/prod.png',
                'badge' => null,
                'is_featured' => false,
                'is_new' => false,
            ],
            [
                'category' => 'closures-frontals',
                'name' => 'Frontal Transparent 13x4',
                'color_label' => 'Transparent naturel',
                'description' => 'Frontal transparent 13x4 ideal pour poses plus larges et coiffages baby hair sur-mesure.',
                'price' => 85000,
                'original_price' => null,
                'stock' => 8,
                'image_url' => '/mams-template/assets/images/pr.png',
                'badge' => null,
                'is_featured' => true,
                'is_new' => false,
            ],
        ];

        foreach ($products as $data) {
            $category = $categories->get($data['category']);

            if (! $category) {
                continue;
            }

            Product::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'length_label' => $data['length_label'] ?? null,
                    'color_label' => $data['color_label'] ?? null,
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'original_price' => $data['original_price'],
                    'stock' => $data['stock'],
                    'image_url' => $data['image_url'],
                    'badge' => $data['badge'],
                    'is_active' => true,
                    'is_featured' => $data['is_featured'],
                    'is_new' => $data['is_new'],
                ]
            );
        }
    }
}
