<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'author_id', 'author', 'genre', 'description', 'price', 'stock', 'is_active'])]
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
     * @return BelongsTo<Author, $this>
     */
    public function authorRecord(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'author_id' => 'integer',
        ];
    }
}
