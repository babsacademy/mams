<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'CMD-'.strtoupper(Str::random(10)),
            'customer_name' => fake()->name(),
            'customer_phone' => '7'.fake()->numerify('########'),
            'customer_email' => fake()->optional()->safeEmail(),
            'customer_address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Dakar', 'Thiès', 'Saint-Louis', 'Rufisque', 'Pikine', 'Guédiawaye']),
            'total' => fake()->randomElement([45000, 72000, 85000, 110000, 130000, 167000]),
            'status' => fake()->randomElement(array_keys(Order::STATUSES)),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
