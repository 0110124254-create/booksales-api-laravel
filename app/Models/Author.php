<?php

namespace App\Models;

class Author
{
    public static function all(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Andrea Hirata',
                'nationality' => 'Indonesia',
                'description' => 'Penulis novel Laskar Pelangi.',
            ],
            [
                'id' => 2,
                'name' => 'Tere Liye',
                'nationality' => 'Indonesia',
                'description' => 'Penulis novel Bumi dan Hujan.',
            ],
            [
                'id' => 3,
                'name' => 'Dewi Lestari',
                'nationality' => 'Indonesia',
                'description' => 'Penulis serial novel Supernova.',
            ],
            [
                'id' => 4,
                'name' => 'Ahmad Fuadi',
                'nationality' => 'Indonesia',
                'description' => 'Penulis novel Negeri 5 Menara.',
            ],
            [
                'id' => 5,
                'name' => 'Pramoedya Ananta Toer',
                'nationality' => 'Indonesia',
                'description' => 'Penulis Tetralogi Buru.',
            ],
        ];
    }
}
