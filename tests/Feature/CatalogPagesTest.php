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

    public function test_web_root_shows_books_and_api_root_keeps_returning_json(): void
    {
        Book::factory()->create(['title' => 'Buku API']);

        $this->get('/')
            ->assertOk()
            ->assertViewIs('catalog.home')
            ->assertSeeText('Buku API')
            ->assertSee('alt="Sampul buku Buku API"', false)
            ->assertSee('src="'.asset('images/books/book-default.svg').'"', false)
            ->assertSee('href="'.route('catalog.genres.index').'"', false)
            ->assertSee('href="'.route('catalog.authors.index').'"', false);

        $this->getJson('/api')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['title' => 'Buku API']);
    }

    public function test_book_catalog_displays_local_cover_images_for_seeded_books(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('src="'.asset('images/books/belajar-sains-dasar.svg').'"', false)
            ->assertSee('src="'.asset('images/books/catatan-kehidupan.svg').'"', false)
            ->assertSee('src="'.asset('images/books/mengenal-teknologi.svg').'"', false)
            ->assertSee('src="'.asset('images/books/petualangan-di-pulau.svg').'"', false)
            ->assertSee('src="'.asset('images/books/sejarah-nusantara.svg').'"', false);
    }

    public function test_genre_view_displays_five_records_as_model_arrays(): void
    {
        $response = $this->get('/genres')
            ->assertOk()
            ->assertViewIs('genres.index')
            ->assertSeeText('Fiksi')
            ->assertSeeText('Teknologi');

        $genres = $response->viewData('genres');

        $this->assertIsArray($genres);
        $this->assertCount(5, $genres);
    }

    public function test_author_view_displays_five_seeded_records_as_model_arrays(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/authors')
            ->assertOk()
            ->assertViewIs('authors.index')
            ->assertSeeText('Andrea Hirata')
            ->assertSeeText('Pramoedya Ananta Toer');

        $authors = $response->viewData('authors');

        $this->assertIsArray($authors);
        $this->assertCount(5, $authors);
    }

    public function test_genre_and_author_views_escape_untrusted_model_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $script = '<script>alert("xss")</script>';
        $genre = Genre::query()->firstOrFail();
        $genre->update(['description' => $script]);
        $author = Author::query()->firstOrFail();
        $author->update(['description' => $script]);

        $this->get('/genres')
            ->assertOk()
            ->assertDontSee($script, false)
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);

        $this->get('/authors')
            ->assertOk()
            ->assertDontSee($script, false)
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
    }

    public function test_admin_can_log_in_through_the_browser_and_open_the_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'a-strong-password',
            'is_admin' => true,
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertViewIs('auth.login')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);

        $this->post('/login', [
            'email' => ' ADMIN@EXAMPLE.TEST ',
            'password' => 'a-strong-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'web');

        $this->get('/admin')
            ->assertOk()
            ->assertViewIs('admin.dashboard')
            ->assertSeeText('admin@example.test');
    }

    public function test_browser_login_rejects_non_admin_accounts(): void
    {
        User::factory()->create([
            'email' => 'customer@example.test',
            'password' => 'a-strong-password',
        ]);

        $this->post('/login', [
            'email' => 'customer@example.test',
            'password' => 'a-strong-password',
        ])->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_browser_login_rejects_an_incorrect_admin_password(): void
    {
        User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'a-strong-password',
            'is_admin' => true,
        ]);

        $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'incorrect-password',
        ])->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_browser_login_requires_email_and_password(): void
    {
        $this->post('/login', [])
            ->assertRedirect()
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest('web');
    }

    public function test_guest_is_redirected_to_login_from_the_browser_dashboard(): void
    {
        $this->get('/admin')
            ->assertRedirect(route('login'));

        $this->assertGuest('web');
    }

    public function test_only_admins_can_open_the_browser_dashboard(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer, 'web')
            ->get('/admin')
            ->assertForbidden();

        $this->assertAuthenticatedAs($customer, 'web');
    }

    public function test_admin_can_log_out_of_the_browser_session(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin, 'web')
            ->post('/logout')
            ->assertRedirect(route('home'));

        $this->assertGuest('web');
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
