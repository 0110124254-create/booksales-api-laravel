<?php

namespace Database\Seeders;

use App\Models\Author;
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
            $author = Author::query()->where('name', $book['author'])->firstOrFail();
            $book['author_id'] = $author->id;
            $storedBook = Book::query()->firstOrCreate(['title' => $book['title']], $book);

            if ($storedBook->author_id === null) {
                $storedBook->update([
                    'author_id' => $author->id,
                    'author' => $author->name,
                ]);
            }
        }

        // Legacy demo titles have no verifiable ISBN; keep them manageable in admin, not in the public catalog.
        Book::query()
            ->whereIn('title', array_column($books, 'title'))
            ->whereNull('isbn')
            ->update(['is_active' => false]);

        $realBooks = [
            ['title' => 'Laskar Pelangi', 'author' => 'Andrea Hirata', 'genre' => 'Fiksi', 'description' => 'Kisah sepuluh anak Belitung yang menjaga mimpi dan semangat belajar di tengah keterbatasan.', 'price' => 99000, 'stock' => 15, 'isbn' => '9789793062792'],
            ['title' => 'Sang Pemimpi', 'author' => 'Andrea Hirata', 'genre' => 'Fiksi', 'description' => 'Perjalanan Ikal dan Arai mengejar pendidikan dan cita-cita mereka.', 'price' => 92000, 'stock' => 12, 'isbn' => '9789793062921'],
            ['title' => 'Rembulan Tenggelam di Wajahmu', 'author' => 'Tere Liye', 'genre' => 'Fiksi', 'description' => 'Sebuah perjalanan hidup yang membuka kembali lima pertanyaan besar tentang takdir dan pilihan.', 'price' => 105000, 'stock' => 10, 'isbn' => '9786239607449'],
            ['title' => 'Nebula', 'author' => 'Tere Liye', 'genre' => 'Fiksi', 'description' => 'Petualangan dalam semesta serial Bumi karya Tere Liye.', 'price' => 89000, 'stock' => 8, 'isbn' => '9786020639536'],
            ['title' => 'Negeri 5 Menara', 'author' => 'Ahmad Fuadi', 'genre' => 'Fiksi', 'description' => 'Alif dan kawan-kawan belajar mengejar cita-cita dengan mantra man jadda wajada.', 'price' => 98000, 'stock' => 11, 'isbn' => '9789792248616'],
            ['title' => 'Ranah 3 Warna', 'author' => 'Ahmad Fuadi', 'genre' => 'Fiksi', 'description' => 'Kisah Alif mempertahankan impian dengan kesabaran dan kerja keras.', 'price' => 105000, 'stock' => 9, 'isbn' => '9789792263251'],
            ['title' => 'Perahu Kertas', 'author' => 'Dewi Lestari', 'genre' => 'Fiksi', 'description' => 'Kisah Kugy dan Keenan tentang persahabatan, mimpi, dan cinta.', 'price' => 115000, 'stock' => 9, 'isbn' => '9786022910527'],
            ['title' => 'Aroma Karsa', 'author' => 'Dewi Lestari', 'genre' => 'Fiksi', 'description' => 'Novel petualangan tentang pencarian aroma legendaris dan rahasia keluarga.', 'price' => 135000, 'stock' => 7, 'isbn' => '9786022914631'],
            ['title' => 'Bumi Manusia', 'author' => 'Pramoedya Ananta Toer', 'genre' => 'Sejarah', 'description' => 'Kisah Minke, seorang pribumi terpelajar, pada masa kolonial Hindia Belanda.', 'price' => 125000, 'stock' => 8, 'isbn' => '9789798659129'],
            ['title' => 'Anak Semua Bangsa', 'author' => 'Pramoedya Ananta Toer', 'genre' => 'Sejarah', 'description' => 'Kelanjutan perjalanan Minke dalam Tetralogi Buru karya Pramoedya Ananta Toer.', 'price' => 125000, 'stock' => 6, 'isbn' => '9789799731241'],
        ];

        foreach ($realBooks as $book) {
            $author = Author::query()->where('name', $book['author'])->firstOrFail();
            $coverPath = 'images/covers/'.$book['isbn'].'.jpg';
            $bookAttributes = [
                ...$book,
                'author_id' => $author->id,
                'cover' => is_file(public_path($coverPath)) ? $coverPath : null,
            ];
            $storedBook = Book::query()->firstOrCreate(
                ['isbn' => $book['isbn']],
                $bookAttributes,
            );

            if ($storedBook->cover === null && is_file(public_path($coverPath))) {
                $storedBook->update(['cover' => $coverPath]);
            }
        }
    }
}
