<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'order_number' => strtoupper(Str::random(10)),
            'user_id' => User::factory(), // Creates a new user if none exists
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'discount_amount' => $this->faker->randomFloat(2, 0, 500),
        ];
    }
}
