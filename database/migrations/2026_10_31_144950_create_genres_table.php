<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('genres')->insert([
            [
                'id' => 1,
                'name' => 'Fiksi',
                'description' => 'Cerita imajinatif dan karya sastra.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Nonfiksi',
                'description' => 'Buku berdasarkan fakta dan kejadian nyata.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Pendidikan',
                'description' => 'Buku untuk belajar dan menambah pengetahuan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Sejarah',
                'description' => 'Buku tentang peristiwa dan tokoh masa lalu.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Teknologi',
                'description' => 'Buku tentang teknologi dan ilmu terapan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genres');
    }
};
