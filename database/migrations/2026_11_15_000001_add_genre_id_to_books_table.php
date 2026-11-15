<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->foreignId('genre_id')->nullable()->after('author_id')->constrained()->nullOnDelete();
        });

        $genreIds = DB::table('genres')->pluck('id', 'name');

        DB::table('books')->select(['id', 'genre'])->orderBy('id')->get()->each(
            static function (object $book) use ($genreIds): void {
                if ($genreIds->has($book->genre)) {
                    DB::table('books')->where('id', $book->id)->update(['genre_id' => $genreIds->get($book->genre)]);
                }
            },
        );
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('genre_id');
        });
    }
};
