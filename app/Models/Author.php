<?php

namespace App\Models;

use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'nationality', 'description'])]
class Author extends Model
{
    /** @use HasFactory<AuthorFactory> */
    use HasFactory;

    /**
     * @return HasMany<Book, $this>
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'author_id');
    }

    /**
     * @return list<array{id: int, name: string, nationality: ?string, description: ?string, books_count: int}>
     */
    public static function catalogData(): array
    {
        return static::query()
            ->withCount('books')
            ->orderBy('id')
            ->get()
            ->map(static fn (self $author): array => [
                'id' => (int) $author->getKey(),
                'name' => $author->name,
                'nationality' => $author->nationality,
                'description' => $author->description,
                'books_count' => (int) $author->books_count,
            ])
            ->all();
    }
}
