<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FetchBookCovers extends Command
{
    protected $signature = 'books:fetch-covers';

    protected $description = 'Download book cover photographs from Open Library by ISBN';

    public function handle(): int
    {
        $directory = public_path('images/covers');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $books = Book::query()->whereNotNull('isbn')->orderBy('id')->get();
        $saved = 0;
        $skipped = 0;

        foreach ($books as $book) {
            $isbn = preg_replace('/[^0-9Xx]/', '', $book->isbn ?? '');
            if ($isbn === '') {
                $skipped++;
                $this->warn("Skipped book {$book->id}: ISBN is empty or invalid.");
                Log::warning('Book cover skipped: ISBN missing', ['book_id' => $book->id]);
                continue;
            }

            try {
                $response = Http::timeout(20)->retry(2, 300, throw: false)
                    ->get("https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg", ['default' => 'false']);
            } catch (ConnectionException $exception) {
                $skipped++;
                $this->warn("Could not fetch ISBN {$isbn}: {$exception->getMessage()}");
                Log::warning('Open Library cover request failed', ['book_id' => $book->id, 'isbn' => $isbn]);
                continue;
            }

            if (! $response->successful() || ! str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                $skipped++;
                $this->warn("No cover found for ISBN {$isbn} ({$book->title}).");
                Log::info('Open Library cover not found', ['book_id' => $book->id, 'isbn' => $isbn]);
                continue;
            }

            $filename = Str::slug($isbn).'.jpg';
            file_put_contents($directory.DIRECTORY_SEPARATOR.$filename, $response->body());
            $book->update(['cover' => 'images/covers/'.$filename]);
            $saved++;
            $this->info("Saved cover for ISBN {$isbn}.");
        }

        $this->info("Finished: {$saved} cover(s) saved, {$skipped} ISBN(s) skipped.");

        return self::SUCCESS;
    }
}
