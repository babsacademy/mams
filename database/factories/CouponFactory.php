<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code'       => strtoupper(fake()->unique()->bothify('????##')),
            'type'       => fake()->randomElement(['percent', 'fixed']),
            'value'      => fake()->randomElement([10, 15, 20, 5000, 10000]),
            'min_order'  => null,
            'max_uses'   => null,
            'uses_count' => 0,
            'expires_at' => null,
            'is_active'  => true,
        ];
    }
}
