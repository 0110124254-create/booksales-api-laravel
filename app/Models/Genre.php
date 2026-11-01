<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description'])]
class Genre extends Model
{
    /**
     * @return list<array{id: int, name: string, description: ?string}>
     */
    public static function catalogData(): array
    {
        return static::query()
            ->orderBy('id')
            ->get()
            ->map(static fn (self $genre): array => [
                'id' => (int) $genre->getKey(),
                'name' => $genre->name,
                'description' => $genre->description,
            ])
            ->all();
    }
}
