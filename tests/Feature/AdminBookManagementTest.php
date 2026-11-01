<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_books_from_the_dashboard(): void
    {
        $author = Author::factory()->create(['name' => 'Penulis Katalog']);
        $genre = Genre::query()->firstOrFail();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->get('/admin')
            ->assertOk()
            ->assertSee(route('admin.web-books.index'));
        $this->get('/admin/books')
            ->assertOk()
            ->assertSeeText('Kelola buku');
        $this->get('/admin/books/create')
            ->assertOk()
            ->assertSeeText('Tambah buku')
            ->assertSeeText($author->name)
            ->assertSeeText($genre->name);

        $this->post('/admin/books', [
            'title' => 'Buku Baru',
            'author_id' => $author->id,
            'genre' => $genre->name,
            'description' => 'Deskripsi buku baru.',
            'price' => 65000,
            'stock' => 8,
            'is_active' => '1',
        ])->assertRedirect(route('admin.web-books.index'))
            ->assertSessionHas('status', 'Buku berhasil ditambahkan.');

        $book = Book::query()->where('title', 'Buku Baru')->firstOrFail();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'author_id' => $author->id,
            'author' => $author->name,
            'genre' => $genre->name,
            'price' => 65000,
            'stock' => 8,
            'is_active' => true,
        ]);

        $this->get(route('admin.web-books.edit', $book))
            ->assertOk()
            ->assertSeeText('Edit buku')
            ->assertSee('value="Buku Baru"', false);

        $this->put(route('admin.web-books.update', $book), [
            'title' => 'Buku Diperbarui',
            'author_id' => $author->id,
            'genre' => $genre->name,
            'description' => 'Deskripsi diperbarui.',
            'price' => 70000,
            'stock' => 0,
            'is_active' => '0',
        ])->assertRedirect(route('admin.web-books.index'))
            ->assertSessionHas('status', 'Buku berhasil diperbarui.');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Buku Diperbarui',
            'price' => 70000,
            'stock' => 0,
            'is_active' => false,
        ]);
        $this->get('/')
            ->assertOk()
            ->assertDontSeeText('Buku Diperbarui');

        $this->delete(route('admin.web-books.destroy', $book))
            ->assertRedirect(route('admin.web-books.index'))
            ->assertSessionHas('status', 'Buku berhasil dihapus.');

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_guests_and_non_admin_users_cannot_manage_books(): void
    {
        $book = Book::factory()->create();

        $this->get('/admin/books')->assertRedirect(route('login'));
        $this->post('/admin/books', [])->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create());

        $this->get('/admin/books')->assertForbidden();
        $this->post('/admin/books', [])->assertForbidden();
        $this->put(route('admin.web-books.update', $book), [])->assertForbidden();
        $this->delete(route('admin.web-books.destroy', $book))->assertForbidden();

        $this->assertModelExists($book);
    }

    public function test_book_form_rejects_invalid_authors_genres_and_prices(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->from(route('admin.web-books.create'))
            ->post('/admin/books', [
                'title' => 'Buku Tidak Valid',
                'author_id' => 99999,
                'genre' => 'Genre tidak terdaftar',
                'description' => null,
                'price' => -1,
                'stock' => 0,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.web-books.create'))
            ->assertSessionHasErrors(['author_id', 'genre', 'price']);

        $this->assertDatabaseMissing('books', ['title' => 'Buku Tidak Valid']);
    }

    public function test_admin_cannot_delete_a_book_that_has_order_items(): void
    {
        $book = Book::factory()->create();
        SaleItem::factory()->create([
            'book_id' => $book->id,
            'book_title' => $book->title,
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->delete(route('admin.web-books.destroy', $book))
            ->assertRedirect(route('admin.web-books.index'))
            ->assertSessionHas('error', 'Buku yang sudah tercatat dalam pesanan tidak dapat dihapus.');

        $this->assertModelExists($book);
    }
}
