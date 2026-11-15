<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $examples = [
            ['title' => 'Petualangan di Pulau', 'author_name' => 'Andrea Hirata', 'genre_name' => 'Fiksi', 'description' => 'Kisah persahabatan dan petualangan.', 'price' => 85000, 'stock' => 12],
            ['title' => 'Belajar Sains Dasar', 'author_name' => 'Tere Liye', 'genre_name' => 'Pendidikan', 'description' => 'Pengantar sains untuk pembaca umum.', 'price' => 72000, 'stock' => 8],
            ['title' => 'Sejarah Nusantara', 'author_name' => 'Ahmad Fuadi', 'genre_name' => 'Sejarah', 'description' => 'Ringkasan peristiwa penting di Nusantara.', 'price' => 98000, 'stock' => 6],
            ['title' => 'Mengenal Teknologi', 'author_name' => 'Dewi Lestari', 'genre_name' => 'Teknologi', 'description' => 'Panduan awal memahami teknologi digital.', 'price' => 110000, 'stock' => 5],
            ['title' => 'Catatan Kehidupan', 'author_name' => 'Pramoedya Ananta Toer', 'genre_name' => 'Nonfiksi', 'description' => 'Kumpulan catatan dan refleksi.', 'price' => 90000, 'stock' => 10],
        ];

        foreach ($examples as $book) {
            Book::query()->updateOrCreate(
                ['title' => $book['title']],
                $this->relations($book) + ['is_active' => false],
            );
        }

        $realBooks = [
            ['title' => 'Laskar Pelangi', 'author_name' => 'Andrea Hirata', 'genre_name' => 'Fiksi', 'description' => 'Kisah sepuluh anak Belitung yang menjaga mimpi dan semangat belajar di tengah keterbatasan.', 'price' => 99000, 'stock' => 15, 'isbn' => '9789793062792'],
            ['title' => 'Sang Pemimpi', 'author_name' => 'Andrea Hirata', 'genre_name' => 'Fiksi', 'description' => 'Perjalanan Ikal dan Arai mengejar pendidikan dan cita-cita mereka.', 'price' => 92000, 'stock' => 12, 'isbn' => '9789793062921'],
            ['title' => 'Rembulan Tenggelam di Wajahmu', 'author_name' => 'Tere Liye', 'genre_name' => 'Fiksi', 'description' => 'Sebuah perjalanan hidup yang membuka kembali lima pertanyaan besar tentang takdir dan pilihan.', 'price' => 105000, 'stock' => 10, 'isbn' => '9786239607449'],
            ['title' => 'Nebula', 'author_name' => 'Tere Liye', 'genre_name' => 'Fiksi', 'description' => 'Petualangan dalam semesta serial Bumi karya Tere Liye.', 'price' => 89000, 'stock' => 8, 'isbn' => '9786020639536'],
            ['title' => 'Negeri 5 Menara', 'author_name' => 'Ahmad Fuadi', 'genre_name' => 'Fiksi', 'description' => 'Alif dan kawan-kawan belajar mengejar cita-cita dengan mantra man jadda wajada.', 'price' => 98000, 'stock' => 11, 'isbn' => '9789792248616'],
            ['title' => 'Ranah 3 Warna', 'author_name' => 'Ahmad Fuadi', 'genre_name' => 'Fiksi', 'description' => 'Kisah Alif mempertahankan impian dengan kesabaran dan kerja keras.', 'price' => 105000, 'stock' => 9, 'isbn' => '9789792263251'],
            ['title' => 'Perahu Kertas', 'author_name' => 'Dewi Lestari', 'genre_name' => 'Fiksi', 'description' => 'Kisah Kugy dan Keenan tentang persahabatan, mimpi, dan cinta.', 'price' => 115000, 'stock' => 9, 'isbn' => '9786022910527'],
            ['title' => 'Aroma Karsa', 'author_name' => 'Dewi Lestari', 'genre_name' => 'Fiksi', 'description' => 'Novel petualangan tentang pencarian aroma legendaris dan rahasia keluarga.', 'price' => 135000, 'stock' => 7, 'isbn' => '9786022914631'],
            ['title' => 'Bumi Manusia', 'author_name' => 'Pramoedya Ananta Toer', 'genre_name' => 'Sejarah', 'description' => 'Kisah Minke, seorang pribumi terpelajar, pada masa kolonial Hindia Belanda.', 'price' => 125000, 'stock' => 8, 'isbn' => '9789798659129'],
            ['title' => 'Anak Semua Bangsa', 'author_name' => 'Pramoedya Ananta Toer', 'genre_name' => 'Sejarah', 'description' => 'Kelanjutan perjalanan Minke dalam Tetralogi Buru karya Pramoedya Ananta Toer.', 'price' => 125000, 'stock' => 6, 'isbn' => '9789799731241'],
        ];

        foreach ($realBooks as $book) {
            $cover = 'images/covers/'.$book['isbn'].'.jpg';
            $bookData = $this->relations($book) + [
                'isbn' => $book['isbn'],
                'cover' => is_file(public_path($cover)) ? $cover : null,
                'is_active' => true,
            ];

            Book::query()->updateOrCreate(['isbn' => $book['isbn']], $bookData);
        }
    }

    /** @param array<string, mixed> $book @return array<string, mixed> */
    private function relations(array $book): array
    {
        return [
            ...collect($book)->except(['author_name', 'genre_name'])->all(),
            'author_id' => Author::query()->where('name', $book['author_name'])->firstOrFail()->id,
            'genre_id' => Genre::query()->where('name', $book['genre_name'])->firstOrFail()->id,
        ];
    }
}
