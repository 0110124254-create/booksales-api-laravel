<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Fiksi', 'description' => 'Cerita imajinatif dan karya sastra.'],
            ['name' => 'Nonfiksi', 'description' => 'Buku berdasarkan fakta dan kejadian nyata.'],
            ['name' => 'Pendidikan', 'description' => 'Buku untuk belajar dan menambah pengetahuan.'],
            ['name' => 'Sejarah', 'description' => 'Buku tentang peristiwa dan tokoh masa lalu.'],
            ['name' => 'Teknologi', 'description' => 'Buku tentang teknologi dan ilmu terapan.'],
        ] as $genre) {
            Genre::query()->updateOrCreate(['name' => $genre['name']], $genre);
        }
    }
}
