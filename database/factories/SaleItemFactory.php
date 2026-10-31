<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'book_id' => Book::factory(),
            'book_title' => fake()->sentence(3),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fake()->numberBetween(20000, 200000),
        ];
    }
}
