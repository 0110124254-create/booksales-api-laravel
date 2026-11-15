<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'author_id', 'genre_id', 'description', 'price', 'stock', 'weight_gram', 'is_active', 'isbn', 'cover'])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * @return HasMany<BookReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(BookReview::class);
    }

    /**
     * @return BelongsTo<Author, $this>
     */
    public function authorRecord(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    /**
     * @return BelongsTo<Genre, $this>
     */
    public function genreRecord(): BelongsTo
    {
        return $this->belongsTo(Genre::class, 'genre_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'weight_gram' => 'integer',
            'is_active' => 'boolean',
            'author_id' => 'integer',
            'genre_id' => 'integer',
        ];
    }
}
