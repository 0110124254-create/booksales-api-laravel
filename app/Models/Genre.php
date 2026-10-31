<?php

namespace App\Models;

class Genre
{
    public static function all(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Fiksi',
                'description' => 'Cerita imajinatif dan karya sastra.',
            ],
            [
                'id' => 2,
                'name' => 'Nonfiksi',
                'description' => 'Buku berdasarkan fakta dan kejadian nyata.',
            ],
            [
                'id' => 3,
                'name' => 'Pendidikan',
                'description' => 'Buku untuk belajar dan menambah pengetahuan.',
            ],
            [
                'id' => 4,
                'name' => 'Sejarah',
                'description' => 'Buku tentang peristiwa dan tokoh masa lalu.',
            ],
            [
                'id' => 5,
                'name' => 'Teknologi',
                'description' => 'Buku tentang teknologi dan ilmu terapan.',
            ],
        ];
    }
}
