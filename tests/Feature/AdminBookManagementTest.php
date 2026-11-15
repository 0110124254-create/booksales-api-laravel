<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminBookManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_books_through_the_api(): void
    {
        $author = Author::factory()->create();
        $genre = Genre::query()->firstOrFail();
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));
        $payload = [
            'title' => 'Buku API Baru',
            'author_id' => $author->id,
            'genre_id' => $genre->id,
            'description' => 'Deskripsi buku.',
            'price' => 65000,
            'stock' => 8,
            'is_active' => true,
            'isbn' => '9780000000001',
        ];

        $created = $this->postJson('/api/admin/books', $payload)
            ->assertCreated()
            ->assertJsonPath('data.author_id', $author->id)
            ->assertJsonPath('data.genre_id', $genre->id);
        $bookId = $created->json('data.id');

        $this->putJson("/api/admin/books/{$bookId}", [...$payload, 'title' => 'Buku API Diperbarui'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Buku API Diperbarui');
        $this->getJson('/api/admin/books')->assertOk()->assertJsonFragment(['title' => 'Buku API Diperbarui']);
        $this->deleteJson("/api/admin/books/{$bookId}")->assertOk();
        $this->assertDatabaseMissing('books', ['id' => $bookId]);
    }

    public function test_admin_can_upload_and_replace_book_covers_through_the_api(): void
    {
        $author = Author::factory()->create();
        $genre = Genre::query()->firstOrFail();
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));
        $fields = [
            'title' => 'Buku Sampul API',
            'author_id' => $author->id,
            'genre_id' => $genre->id,
            'price' => 45000,
            'stock' => 5,
            'is_active' => '1',
        ];

        $coverOne = UploadedFile::fake()->createWithContent('cover-one.jpg', File::get(public_path('images/covers/9786020639536.jpg')));
        $coverTwo = UploadedFile::fake()->createWithContent('cover-two.jpg', File::get(public_path('images/covers/9786022910527.jpg')));
        $this->post('/api/admin/books', [...$fields, 'cover_upload' => $coverOne])->assertCreated();
        $book = Book::query()->where('title', $fields['title'])->firstOrFail();
        $oldCover = $book->cover;
        $this->assertFileExists(public_path($oldCover));

        $this->patch("/api/admin/books/{$book->id}", ['cover_upload' => $coverTwo])->assertOk();
        $book->refresh();
        $this->assertNotSame($oldCover, $book->cover);
        $this->assertFileDoesNotExist(public_path($oldCover));
        File::delete(public_path($book->cover));
    }

    public function test_admin_book_api_validates_data_and_denies_guests_and_customers(): void
    {
        $this->getJson('/api/admin/books')->assertUnauthorized();
        $customer = User::factory()->create();
        Sanctum::actingAs($customer);
        $this->getJson('/api/admin/books')->assertForbidden();
        $this->postJson('/api/admin/books', [])->assertForbidden();

        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));
        $this->postJson('/api/admin/books', [
            'title' => '',
            'author_id' => 99999,
            'genre_id' => 99999,
            'price' => -1,
            'stock' => -1,
        ])->assertUnprocessable()->assertJsonValidationErrors(['title', 'author_id', 'genre_id', 'price', 'stock']);
    }

    public function test_admin_cannot_delete_a_book_that_has_order_items(): void
    {
        $book = Book::factory()->create();
        SaleItem::factory()->create(['book_id' => $book->id, 'book_title' => $book->title]);
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));

        $this->deleteJson("/api/admin/books/{$book->id}")->assertStatus(409);
        $this->assertModelExists($book);
    }
}
