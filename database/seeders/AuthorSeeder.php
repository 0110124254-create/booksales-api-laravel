<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = [
            [
                'name' => 'Andrea Hirata',
                'nationality' => 'Indonesia',
                'description' => 'Penulis novel Laskar Pelangi.',
            ],
            [
                'name' => 'Tere Liye',
                'nationality' => 'Indonesia',
                'description' => 'Penulis novel Bumi dan Hujan.',
            ],
            [
                'name' => 'Dewi Lestari',
                'nationality' => 'Indonesia',
                'description' => 'Penulis serial novel Supernova.',
            ],
            [
                'name' => 'Ahmad Fuadi',
                'nationality' => 'Indonesia',
                'description' => 'Penulis novel Negeri 5 Menara.',
            ],
            [
                'name' => 'Pramoedya Ananta Toer',
                'nationality' => 'Indonesia',
                'description' => 'Penulis Tetralogi Buru.',
            ],
        ];

        foreach ($authors as $author) {
            Author::query()->updateOrCreate(['name' => $author['name']], $author);
        }
    }
}
