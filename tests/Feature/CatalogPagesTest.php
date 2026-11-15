<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_routes_serve_the_react_shell_and_api_routes_remain_json(): void
    {
        foreach (['/', '/books', '/about', '/login', '/register', '/cart', '/admin/books'] as $path) {
            $this->get($path)->assertOk()->assertViewIs('app')->assertSee('id="root"', false);
        }

        $this->getJson('/api')->assertOk()->assertJsonPath('success', true);
    }

    public function test_public_genre_and_author_api_returns_seeded_catalog_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->getJson('/api/genres')->assertOk()->assertJsonCount(5, 'data')->assertJsonFragment(['name' => 'Fiksi']);
        $this->getJson('/api/authors')->assertOk()->assertJsonCount(5, 'data')->assertJsonFragment(['name' => 'Andrea Hirata']);
        $this->getJson('/api/books?q=Laskar%20Pelangi')->assertOk()->assertJsonPath('data.0.title', 'Laskar Pelangi');
    }

    public function test_admin_can_manage_genres_with_foreign_key_book_relationships(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Sanctum::actingAs($admin);

        $created = $this->postJson('/api/genres', ['name' => 'Genre SPA', 'description' => 'Genre untuk pengujian.'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Genre SPA');
        $genre = Genre::query()->findOrFail($created->json('data.id'));
        $book = Book::factory()->create(['genre_id' => $genre->id, 'genre' => $genre->name]);

        $this->patchJson("/api/genres/{$genre->id}", ['name' => 'Genre React'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Genre React');
        $this->assertSame($genre->id, $book->fresh()->genre_id);
        $this->deleteJson("/api/genres/{$genre->id}")->assertStatus(409);

        $book->delete();
        $this->deleteJson("/api/genres/{$genre->id}")->assertOk();
    }

    public function test_admin_can_manage_authors_and_customer_writes_are_forbidden(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/authors', ['name' => 'Tidak Diizinkan'])->assertForbidden();

        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));
        $created = $this->postJson('/api/authors', ['name' => 'Penulis SPA', 'nationality' => 'Indonesia'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Penulis SPA');
        $author = Author::query()->findOrFail($created->json('data.id'));
        $book = Book::factory()->create(['author_id' => $author->id, 'author' => $author->name]);

        $this->patchJson("/api/authors/{$author->id}", ['name' => 'Penulis React'])->assertOk();
        $this->assertSame('Penulis React', $book->fresh()->authorRecord->name);
        $this->deleteJson("/api/authors/{$author->id}")->assertStatus(409);
    }
}
