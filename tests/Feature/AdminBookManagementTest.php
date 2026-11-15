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
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminBookManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_and_replace_a_real_book_cover(): void
    {
        $author = Author::factory()->create();
        $genre = Genre::query()->firstOrFail();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $fields = [
            'title' => 'Buku Dengan Sampul',
            'author_id' => $author->id,
            'genre' => $genre->name,
            'price' => 45000,
            'stock' => 5,
            'is_active' => '1',
        ];
        $temporaryDirectory = storage_path('framework/testing/book-cover-uploads');
        File::ensureDirectoryExists($temporaryDirectory);
        $firstSource = $temporaryDirectory.'/first-'.Str::uuid().'.jpg';
        $secondSource = $temporaryDirectory.'/second-'.Str::uuid().'.jpg';
        File::copy(public_path('images/covers/9786020639536.jpg'), $firstSource);
        File::copy(public_path('images/covers/9786022910527.jpg'), $secondSource);
        $firstImage = new UploadedFile($firstSource, 'sampul.jpg', 'image/jpeg', null, true);

        $this->post(route('admin.web-books.store'), [...$fields, 'cover_upload' => $firstImage])
            ->assertRedirect(route('admin.web-books.index'));

        $book = Book::query()->where('title', $fields['title'])->firstOrFail();
        $oldCover = $book->cover;
        $this->assertStringStartsWith('images/covers/uploads/', $oldCover);
        $this->assertFileExists(public_path($oldCover));

        $secondImage = new UploadedFile($secondSource, 'sampul-baru.jpg', 'image/jpeg', null, true);
        $this->put(route('admin.web-books.update', $book), [...$fields, 'cover_upload' => $secondImage])
            ->assertRedirect(route('admin.web-books.index'));

        $book->refresh();
        $this->assertNotSame($oldCover, $book->cover);
        $this->assertFileDoesNotExist(public_path($oldCover));
        $this->assertFileExists(public_path($book->cover));

        File::delete(public_path($book->cover));
        File::delete([$firstSource, $secondSource]);
        $this->assertFileExists(public_path('images/covers/9786020639536.jpg'));
        $this->assertFileExists(public_path('images/covers/9786022910527.jpg'));
    }

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
            'isbn' => '9789793062792',
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
            'isbn' => '9789793062792',
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
            'isbn' => '9789793062921',
        ])->assertRedirect(route('admin.web-books.index'))
            ->assertSessionHas('status', 'Buku berhasil diperbarui.');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Buku Diperbarui',
            'price' => 70000,
            'stock' => 0,
            'is_active' => false,
            'isbn' => '9789793062921',
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
