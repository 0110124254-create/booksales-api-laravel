<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookSalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_catalog_returns_books_and_the_api_root(): void
    {
        Book::factory()->create([
            'title' => 'Buku Contoh',
            'stock' => 4,
            'price' => 45000,
        ]);

        $this->getJson('/api')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Buku Contoh']);

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonPath('message', 'Get All Resource')
            ->assertJsonFragment(['title' => 'Buku Contoh']);
    }

    public function test_database_seeder_creates_five_authors_and_five_linked_books(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('authors', 5);
        $this->assertDatabaseCount('books', 5);
        $this->assertSame(5, Book::query()->whereNotNull('author_id')->count());
        $this->assertSame(1, Author::query()->where('name', 'Andrea Hirata')->firstOrFail()->books()->count());
    }

    public function test_checkout_records_order_and_decreases_stock(): void
    {
        $book = Book::factory()->create([
            'price' => 30000,
            'stock' => 5,
        ]);

        $response = $this->postJson('/api/checkout', [
            'customer_name' => 'Pelanggan Contoh',
            'customer_email' => 'pelanggan@example.test',
            'customer_phone' => '08123456789',
            'payment_method' => 'bank_transfer',
            'items' => [
                ['book_id' => $book->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total', 60000)
            ->assertJsonPath('data.items.0.quantity', 2);

        $reference = $response->json('data.reference');
        $this->getJson("/api/orders/{$reference}")
            ->assertOk()
            ->assertJsonPath('data.reference', $reference);

        $this->assertSame(3, $book->fresh()->stock);
        $this->assertDatabaseHas('sale_items', [
            'book_id' => $book->id,
            'quantity' => 2,
            'unit_price' => 30000,
        ]);
    }

    public function test_checkout_rejects_insufficient_stock_without_partial_changes(): void
    {
        $firstBook = Book::factory()->create(['stock' => 5, 'price' => 20000]);
        $secondBook = Book::factory()->create(['stock' => 1]);

        $this->postJson('/api/checkout', [
            'customer_name' => 'Pelanggan Contoh',
            'customer_phone' => '08123456789',
            'payment_method' => 'cash',
            'items' => [
                ['book_id' => $firstBook->id, 'quantity' => 2],
                ['book_id' => $secondBook->id, 'quantity' => 2],
            ],
        ])->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['items']]);

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(5, $firstBook->fresh()->stock);
        $this->assertSame(1, $secondBook->fresh()->stock);
    }

    public function test_book_resource_requires_admin_for_writes_and_supports_crud(): void
    {
        $author = Author::factory()->create(['name' => 'Author Contoh']);
        $body = [
            'title' => 'Buku Admin',
            'author_id' => $author->id,
            'genre' => 'Fiksi',
            'description' => 'Deskripsi buku.',
            'price' => 50000,
            'stock' => 10,
            'is_active' => true,
        ];

        $this->postJson('/api/books', $body)->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));
        $created = $this->postJson('/api/books', $body)
            ->assertCreated()
            ->assertJsonPath('data.author', 'Author Contoh');

        $bookId = $created->json('data.id');
        $this->getJson("/api/books/{$bookId}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Buku Admin');

        $this->putJson("/api/books/{$bookId}", [
            ...$body,
            'title' => 'Buku Diperbarui',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Buku Diperbarui');

        $this->patchJson("/api/books/{$bookId}", ['title' => 'Buku Parsial'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Buku Parsial');

        $this->assertDatabaseHas('books', [
            'id' => $bookId,
            'author_id' => $author->id,
            'author' => 'Author Contoh',
            'title' => 'Buku Parsial',
        ]);

        $this->deleteJson("/api/books/{$bookId}")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_log_in_and_log_out_with_a_sanctum_token(): void
    {
        User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'a-strong-password',
            'is_admin' => true,
        ]);

        $login = $this->postJson('/api/login', [
            'email' => ' ADMIN@EXAMPLE.TEST ',
            'password' => 'a-strong-password',
        ])->assertOk()
            ->assertJsonPath('success', true);

        $token = $login->json('data.token');

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', 'admin@example.test');

        $this->withToken($token)->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_confirm_payment_and_cancel_pending_orders_safely(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $book = Book::factory()->create(['stock' => 5, 'price' => 25000]);

        $paidResponse = $this->postJson('/api/checkout', [
            'customer_name' => 'Pelanggan Contoh',
            'customer_phone' => '08123456789',
            'payment_method' => 'cash',
            'items' => [['book_id' => $book->id, 'quantity' => 2]],
        ])->assertCreated();
        $paidSale = Sale::query()->where('reference', $paidResponse->json('data.reference'))->firstOrFail();

        Sanctum::actingAs($admin);
        $this->postJson(route('admin.sales.paid', $paidSale))
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'paid');

        $this->assertSame(3, $book->fresh()->stock);

        $cancelResponse = $this->postJson('/api/checkout', [
            'customer_name' => 'Pelanggan Kedua',
            'customer_phone' => '08123456789',
            'payment_method' => 'bank_transfer',
            'items' => [['book_id' => $book->id, 'quantity' => 1]],
        ])->assertCreated();
        $pendingSale = Sale::query()->where('reference', $cancelResponse->json('data.reference'))->firstOrFail();

        $this->postJson(route('admin.sales.cancel', $pendingSale))
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'cancelled');
        $this->assertSame(3, $book->fresh()->stock);

        $this->postJson(route('admin.sales.cancel', $pendingSale))
            ->assertStatus(409);

        $this->getJson(route('admin.reports.index'))
            ->assertOk()
            ->assertJsonPath('data.paid_revenue', 50000)
            ->assertJsonPath('data.paid_count', 1);
    }

    public function test_customer_cannot_create_orders_for_inactive_books(): void
    {
        $book = Book::factory()->create(['is_active' => false, 'stock' => 5]);

        $this->postJson('/api/checkout', [
            'customer_name' => 'Pelanggan Contoh',
            'customer_phone' => '08123456789',
            'payment_method' => 'cash',
            'items' => [['book_id' => $book->id, 'quantity' => 1]],
        ])->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['items']]);

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(5, $book->fresh()->stock);
    }

    public function test_report_accepts_a_single_start_date_filter(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));

        $this->getJson(route('admin.reports.index', [
            'start_date' => today()->startOfMonth()->toDateString(),
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.start_date', today()->startOfMonth()->toDateString());
    }

    public function test_non_admin_accounts_cannot_sign_in_or_access_admin_routes(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.test',
            'password' => 'a-strong-password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'customer@example.test',
            'password' => 'a-strong-password',
        ])->assertUnauthorized();

        Sanctum::actingAs($user);
        $this->getJson(route('admin.books.index'))->assertForbidden();
    }
}
