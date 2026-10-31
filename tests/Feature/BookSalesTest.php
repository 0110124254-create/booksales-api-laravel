<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_displays_database_books_and_checkout_form(): void
    {
        $book = Book::factory()->create([
            'title' => 'Buku Contoh',
            'stock' => 4,
            'price' => 45000,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Buku Contoh')
            ->assertSee('Rp45.000')
            ->assertSee(route('sales.store'));
    }

    public function test_checkout_records_order_and_decreases_stock(): void
    {
        $book = Book::factory()->create([
            'price' => 30000,
            'stock' => 5,
        ]);

        $response = $this->post(route('sales.store'), [
            'customer_name' => 'Pelanggan Contoh',
            'customer_email' => 'pelanggan@example.test',
            'customer_phone' => '08123456789',
            'payment_method' => 'bank_transfer',
            'items' => [
                ['book_id' => $book->id, 'quantity' => 2],
            ],
        ]);

        $sale = Sale::query()->firstOrFail();
        $response->assertRedirect(route('sales.show', $sale->reference));
        $this->assertSame(3, $book->fresh()->stock);
        $this->assertSame(60000, $sale->total);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'book_id' => $book->id,
            'quantity' => 2,
            'unit_price' => 30000,
        ]);
    }

    public function test_checkout_rejects_insufficient_stock_without_partial_changes(): void
    {
        $firstBook = Book::factory()->create(['stock' => 5, 'price' => 20000]);
        $secondBook = Book::factory()->create(['stock' => 1]);

        $this->from('/')
            ->post(route('sales.store'), [
                'customer_name' => 'Pelanggan Contoh',
                'customer_phone' => '08123456789',
                'payment_method' => 'cash',
                'items' => [
                    ['book_id' => $firstBook->id, 'quantity' => 2],
                    ['book_id' => $secondBook->id, 'quantity' => 2],
                ],
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(5, $firstBook->fresh()->stock);
        $this->assertSame(1, $secondBook->fresh()->stock);
    }

    public function test_book_management_requires_admin_and_validates_book_data(): void
    {
        $this->get(route('admin.books.index'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.books.store'), [
                'title' => 'Buku Admin',
                'author' => 'Author Contoh',
                'genre' => 'Fiksi',
                'description' => 'Deskripsi buku.',
                'price' => 50000,
                'stock' => 10,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.books.index'));

        $this->assertDatabaseHas('books', [
            'title' => 'Buku Admin',
            'price' => 50000,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_log_in_and_log_out(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'a-strong-password',
            'is_admin' => true,
        ]);

        $this->post(route('login.store'), [
            'email' => ' ADMIN@EXAMPLE.TEST ',
            'password' => 'a-strong-password',
        ])->assertRedirect(route('admin.books.index'));

        $this->assertAuthenticatedAs($admin);

        $this->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_admin_can_confirm_payment_and_cancel_pending_orders_safely(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $book = Book::factory()->create(['stock' => 5, 'price' => 25000]);

        $this->post(route('sales.store'), [
            'customer_name' => 'Pelanggan Contoh',
            'customer_phone' => '08123456789',
            'payment_method' => 'cash',
            'items' => [['book_id' => $book->id, 'quantity' => 2]],
        ]);
        $paidSale = Sale::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.sales.paid', $paidSale))
            ->assertSessionHas('status');

        $this->assertSame('paid', $paidSale->fresh()->payment_status);
        $this->assertSame(3, $book->fresh()->stock);

        $this->post(route('sales.store'), [
            'customer_name' => 'Pelanggan Kedua',
            'customer_phone' => '08123456789',
            'payment_method' => 'bank_transfer',
            'items' => [['book_id' => $book->id, 'quantity' => 1]],
        ]);
        $pendingSale = Sale::query()->where('payment_status', 'pending')->firstOrFail();

        $this->post(route('admin.sales.cancel', $pendingSale))
            ->assertSessionHas('status');
        $this->assertSame(3, $book->fresh()->stock);

        $this->post(route('admin.sales.cancel', $pendingSale))
            ->assertSessionHasErrors('sale');
        $this->assertSame(3, $book->fresh()->stock);

        $this->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Rp50.000')
            ->assertSee('Laporan Penjualan');
    }

    public function test_customer_cannot_create_orders_for_inactive_books(): void
    {
        $book = Book::factory()->create(['is_active' => false, 'stock' => 5]);

        $this->from('/')
            ->post(route('sales.store'), [
                'customer_name' => 'Pelanggan Contoh',
                'customer_phone' => '08123456789',
                'payment_method' => 'cash',
                'items' => [['book_id' => $book->id, 'quantity' => 1]],
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(5, $book->fresh()->stock);
    }

    public function test_report_accepts_a_single_start_date_filter(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'start_date' => today()->startOfMonth()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Laporan Penjualan');
    }

    public function test_non_admin_accounts_cannot_sign_in_or_access_admin_routes(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.test',
            'password' => 'a-strong-password',
        ]);

        $this->post(route('login.store'), [
            'email' => 'customer@example.test',
            'password' => 'a-strong-password',
        ])->assertSessionHasErrors('email');

        $this->actingAs($user)
            ->get(route('admin.books.index'))
            ->assertForbidden();

        $this->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
