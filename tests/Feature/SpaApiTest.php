<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Genre;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SpaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_use_author_and_genre_foreign_keys_without_legacy_text_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('books', 'author_id'));
        $this->assertTrue(Schema::hasColumn('books', 'genre_id'));
        $this->assertFalse(Schema::hasColumn('books', 'author'));
        $this->assertFalse(Schema::hasColumn('books', 'genre'));

        $genre = Genre::query()->firstOrFail();
        $book = Book::factory()->create(['genre_id' => $genre->id]);
        $genre->delete();
        $this->assertNull($book->fresh()->genre_id);
    }

    public function test_registration_creates_a_customer_and_issues_a_sanctum_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Pengguna Baru',
            'username' => 'penggunabaru',
            'email' => 'baru@example.test',
            'password' => 'password-kuat',
            'password_confirmation' => 'password-kuat',
        ])->assertCreated()->assertJsonPath('data.user.is_admin', false);

        $token = $response->json('data.token');
        $this->withToken($token)->getJson('/api/user')->assertOk()->assertJsonPath('email', 'baru@example.test');
        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => (int) explode('|', $token, 2)[0]]);
    }

    public function test_registration_rejects_a_mismatched_password_confirmation(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Pengguna Baru',
            'username' => 'penggunabaru',
            'email' => 'baru@example.test',
            'password' => 'password-kuat',
            'password_confirmation' => 'yang-berbeda',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_book_api_searches_by_title_author_and_isbn_and_returns_pagination_metadata(): void
    {
        $author = Author::factory()->create(['name' => 'Penulis Pencarian']);
        $genre = Genre::query()->firstOrFail();
        Book::factory()->create([
            'title' => 'Judul Unik',
            'author_id' => $author->id,
            'genre_id' => $genre->id,
            'isbn' => '111-UNIK',
        ]);
        Book::factory()->create(['title' => 'Buku Tidak Cocok']);

        $this->getJson('/api/books?q=Penulis%20Pencarian&per_page=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('data.0.title', 'Judul Unik')
            ->assertJsonPath('data.0.author_record.name', 'Penulis Pencarian');

        $this->getJson('/api/books?q=111-UNIK')->assertJsonPath('meta.total', 1);
    }

    public function test_customer_order_history_only_contains_the_authenticated_users_orders(): void
    {
        $customer = User::factory()->create();
        $book = Book::factory()->create(['stock' => 4]);
        Sanctum::actingAs($customer);
        $created = $this->postJson('/api/checkout', [
            'customer_name' => 'Pelanggan Uji',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Contoh No. 5, Jakarta',
            'payment_method' => 'cash',
            'items' => [['book_id' => $book->id, 'quantity' => 1]],
        ])->assertCreated();

        $this->getJson('/api/my/orders')
            ->assertOk()
            ->assertJsonPath('data.data.0.reference', $created->json('data.reference'));

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/my/orders')->assertJsonPath('data.total', 0);
    }

    public function test_customer_can_send_contact_message_and_admin_can_mark_it_read(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $created = $this->postJson('/api/contact', [
            'name' => 'Pengguna Uji',
            'email' => 'user@example.test',
            'subject' => 'Pertanyaan buku',
            'message' => 'Mohon informasi ketersediaan buku ini.',
        ])->assertCreated();
        $message = ContactMessage::query()->findOrFail($created->json('data.id'));

        $this->getJson('/api/admin/messages')->assertForbidden();
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));
        $this->getJson('/api/admin/messages')->assertOk()->assertJsonFragment(['subject' => 'Pertanyaan buku']);
        $updated = $this->patchJson("/api/admin/messages/{$message->id}")->assertOk();
        $this->assertNotNull($updated->json('data.read_at'));
    }

    public function test_admin_dashboard_and_management_api_reject_non_admin_users(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/admin/dashboard')->assertForbidden();
        $this->getJson('/api/admin/users')->assertForbidden();
        $this->getJson('/api/admin/sales')->assertForbidden();
        $this->getJson('/api/admin/books')->assertForbidden();

        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));
        $this->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.book_count', 0)
            ->assertJsonPath('data.user_count', 1);
        $this->getJson('/api/admin/users')->assertOk();
        $this->getJson('/api/admin/sales')->assertOk();
    }

    public function test_admin_can_confirm_payment_and_update_order_fulfillment_after_checkout(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $book = Book::factory()->create(['stock' => 3]);
        Sanctum::actingAs($customer);
        $created = $this->postJson('/api/checkout', [
            'customer_name' => 'Pelanggan Uji',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Contoh No. 5, Jakarta',
            'payment_method' => 'cash',
            'items' => [['book_id' => $book->id, 'quantity' => 1]],
        ])->assertCreated();
        $sale = Sale::query()->where('reference', $created->json('data.reference'))->firstOrFail();

        Sanctum::actingAs($admin);
        $this->patchJson("/api/admin/sales/{$sale->id}", ['fulfillment_status' => 'processing'])->assertUnprocessable();
        $this->postJson("/api/admin/sales/{$sale->id}/paid")->assertOk();
        $this->patchJson("/api/admin/sales/{$sale->id}", ['fulfillment_status' => 'processing'])
            ->assertOk()
            ->assertJsonPath('data.fulfillment_status', 'processing');

        Sanctum::actingAs($customer);
        $this->patchJson("/api/admin/sales/{$sale->id}", ['fulfillment_status' => 'shipped'])->assertForbidden();
    }
}
