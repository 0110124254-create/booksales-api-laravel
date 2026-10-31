<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $authorName = fake()->name();

        return [
            'title' => fake()->sentence(3),
            'author_id' => Author::factory()->state(['name' => $authorName]),
            'author' => $authorName,
            'genre' => fake()->randomElement(['Fiksi', 'Nonfiksi', 'Pendidikan', 'Sejarah', 'Teknologi']),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(20000, 200000),
            'stock' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
