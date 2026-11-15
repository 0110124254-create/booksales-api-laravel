<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('books')->select(['id', 'author_id', 'author'])->orderBy('id')->get()->each(
            static function (object $book): void {
                if ($book->author_id !== null || trim((string) $book->author) === '') {
                    return;
                }

                $author = DB::table('authors')->where('name', $book->author)->first();
                if ($author === null) {
                    $now = now();
                    DB::table('authors')->insertOrIgnore([
                        'name' => $book->author,
                        'nationality' => null,
                        'description' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $author = DB::table('authors')->where('name', $book->author)->first();
                }

                if ($author !== null) {
                    DB::table('books')->where('id', $book->id)->update(['author_id' => $author->id]);
                }
            },
        );

        Schema::table('books', function (Blueprint $table): void {
            $table->dropColumn(['author', 'genre']);
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->string('author')->nullable();
            $table->string('genre')->nullable();
        });

        DB::table('books')->select(['id', 'author_id', 'genre_id'])->orderBy('id')->get()->each(
            static function (object $book): void {
                DB::table('books')->where('id', $book->id)->update([
                    'author' => $book->author_id === null ? null : DB::table('authors')->where('id', $book->author_id)->value('name'),
                    'genre' => $book->genre_id === null ? null : DB::table('genres')->where('id', $book->genre_id)->value('name'),
                ]);
            },
        );
    }
};
