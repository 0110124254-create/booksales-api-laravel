<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_genre_page_displays_five_genres(): void
    {
        $response = $this->get('/genres');

        $response->assertOk()
            ->assertSee('Daftar Genre')
            ->assertSee('Fiksi')
            ->assertSee('Nonfiksi')
            ->assertSee('Pendidikan')
            ->assertSee('Sejarah')
            ->assertSee('Teknologi');
    }

    public function test_author_page_displays_five_authors(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/authors');

        $response->assertOk()
            ->assertSee('Daftar Author')
            ->assertSee('Andrea Hirata')
            ->assertSee('Tere Liye')
            ->assertSee('Dewi Lestari')
            ->assertSee('Ahmad Fuadi')
            ->assertSee('Pramoedya Ananta Toer')
            ->assertSee('Jumlah Buku');
    }

    public function test_home_page_links_to_both_catalog_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Genre')
            ->assertSee('Author')
            ->assertSee(route('genres.index'))
            ->assertSee(route('authors.index'));
    }
}
