<?php

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => 'BOOK-'.Str::upper(Str::random(12)),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->numerify('08##########'),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer']),
            'payment_status' => 'pending',
            'total' => fake()->numberBetween(20000, 200000),
        ];
    }
}
