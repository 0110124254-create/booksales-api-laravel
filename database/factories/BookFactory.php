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
    public function configure(): static
    {
        return $this->afterMaking(function (Book $book): void {
            $genreId = Genre::query()->where('name', $book->genre)->value('id');
            if ($genreId !== null) {
                $book->genre_id = $genreId;
            }
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $authorName = fake()->name();
        $genre = Genre::query()->inRandomOrder()->first();
        $genre ??= Genre::query()->create(['name' => 'Fiksi']);

        return [
            'title' => fake()->sentence(3),
            'author_id' => Author::factory()->state(['name' => $authorName]),
            'author' => $authorName,
            'genre_id' => $genre->id,
            'genre' => $genre->name,
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(20000, 200000),
            'stock' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
