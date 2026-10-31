<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_genre_endpoint_returns_the_existing_genres(): void
    {
        $this->getJson('/api/genres')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Get All Resource')
            ->assertJsonCount(5, 'data')
            ->assertJsonFragment(['name' => 'Fiksi'])
            ->assertJsonFragment(['name' => 'Teknologi']);
    }

    public function test_author_endpoint_returns_seeded_authors(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->getJson('/api/authors')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonFragment(['name' => 'Andrea Hirata'])
            ->assertJsonFragment(['name' => 'Pramoedya Ananta Toer']);
    }

    public function test_web_root_keeps_the_welcome_page_and_api_root_returns_books(): void
    {
        Book::factory()->create(['title' => 'Buku API']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Laravel');

        $this->getJson('/api')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['title' => 'Buku API']);
    }

    public function test_genre_resource_supports_crud_and_keeps_book_genre_text_in_sync(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Sanctum::actingAs($admin);

        $created = $this->postJson('/api/genres', [
            'name' => 'Genre API',
            'description' => 'Genre untuk pengujian.',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Genre API');

        $genreId = $created->json('data.id');
        $book = Book::factory()->create(['genre' => 'Genre API']);

        $this->getJson("/api/genres/{$genreId}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Genre API');

        $this->putJson("/api/genres/{$genreId}", [
            'name' => 'Genre Diperbarui',
            'description' => null,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Genre Diperbarui');

        $this->assertSame('Genre Diperbarui', $book->fresh()->genre);

        $this->deleteJson("/api/genres/{$genreId}")
            ->assertStatus(409);

        $this->deleteJson('/api/genres/1')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_author_resource_supports_crud_and_protects_authors_used_by_books(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));

        $created = $this->postJson('/api/authors', [
            'name' => 'Author API',
            'nationality' => 'Indonesia',
            'description' => 'Deskripsi author.',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Author API');

        $authorId = $created->json('data.id');
        $book = Book::factory()->create([
            'author_id' => $authorId,
            'author' => 'Author API',
        ]);

        $this->getJson("/api/authors/{$authorId}")
            ->assertOk()
            ->assertJsonPath('data.books_count', 1);

        $this->patchJson("/api/authors/{$authorId}", ['name' => 'Author Diperbarui'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Author Diperbarui');

        $this->assertSame('Author Diperbarui', $book->fresh()->author);
        $this->deleteJson("/api/authors/{$authorId}")->assertStatus(409);

        $book->delete();
        $this->deleteJson("/api/authors/{$authorId}")->assertOk();
    }
}
