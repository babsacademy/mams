<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $names = [
            'Le Signature Noir', 'L\'Élégance Sable', 'Pochette Nuit', 'Porte-cartes Cuir',
            'Le Seau Royal', 'La Classique', 'Mini Cuir Verni', 'Le Tressé Camel',
            'Bandoulière Dorée', 'Clutch Perlée', 'Le Cabas Premium', 'Mini Croco',
        ];
        $name = fake()->unique()->randomElement($names);
        $price = fake()->randomElement([22000, 25000, 38000, 45000, 72000, 85000, 95000, 110000]);
        $isNew = fake()->boolean(30);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(3),
            'length_label' => null,
            'color_label' => null,
            'description' => fake()->paragraph(2),
            'price' => $price,
            'original_price' => fake()->boolean(20) ? (int) ($price * 1.25) : null,
            'stock' => fake()->numberBetween(0, 50),
            'image_url' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?q=80&w=800',
            'badge' => $isNew ? 'Nouveau' : null,
            'is_active' => true,
            'is_featured' => fake()->boolean(25),
            'is_new' => $isNew,
        ];
    }
}
