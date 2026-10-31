<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'title' => 'Petualangan di Pulau',
                'author' => 'Andrea Hirata',
                'genre' => 'Fiksi',
                'description' => 'Kisah persahabatan dan petualangan.',
                'price' => 85000,
                'stock' => 12,
            ],
            [
                'title' => 'Belajar Sains Dasar',
                'author' => 'Tere Liye',
                'genre' => 'Pendidikan',
                'description' => 'Pengantar sains untuk pembaca umum.',
                'price' => 72000,
                'stock' => 8,
            ],
            [
                'title' => 'Sejarah Nusantara',
                'author' => 'Ahmad Fuadi',
                'genre' => 'Sejarah',
                'description' => 'Ringkasan peristiwa penting di Nusantara.',
                'price' => 98000,
                'stock' => 6,
            ],
            [
                'title' => 'Mengenal Teknologi',
                'author' => 'Dewi Lestari',
                'genre' => 'Teknologi',
                'description' => 'Panduan awal memahami teknologi digital.',
                'price' => 110000,
                'stock' => 5,
            ],
            [
                'title' => 'Catatan Kehidupan',
                'author' => 'Pramoedya Ananta Toer',
                'genre' => 'Nonfiksi',
                'description' => 'Kumpulan catatan dan refleksi.',
                'price' => 90000,
                'stock' => 10,
            ],
        ];

        foreach ($books as $book) {
            Book::query()->firstOrCreate(['title' => $book['title']], $book);
        }
    }
}
