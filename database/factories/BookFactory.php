<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
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
        $genre = Genre::query()->inRandomOrder()->first();
        $genre ??= Genre::query()->create(['name' => 'Fiksi']);

        return [
            'title' => fake()->sentence(3),
            'author_id' => Author::factory(),
            'genre_id' => $genre->id,
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(20000, 200000),
            'stock' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
