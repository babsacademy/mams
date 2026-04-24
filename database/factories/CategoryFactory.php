<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Sacs à main', 'Bandoulières', 'Pochettes', 'Accessoires', 'Sacs de soirée']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(3),
            'image_url' => fake()->imageUrl(800, 600, 'fashion'),
            'is_active' => true,
        ];
    }
}
