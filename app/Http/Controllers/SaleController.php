<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'payment_method' => ['required', 'in:cash,bank_transfer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.book_id' => ['required', 'integer', 'distinct', 'exists:books,id'],
            'items.*.quantity' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        $requestedItems = collect($data['items'])
            ->filter(fn (array $item): bool => $item['quantity'] > 0);

        if ($requestedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Pilih setidaknya satu buku dengan jumlah lebih dari nol.',
            ]);
        }

        $sale = DB::transaction(function () use ($data, $requestedItems): Sale {
            $sale = Sale::query()->create([
                'reference' => 'BOOK-'.Str::upper(Str::random(12)),
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'],
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'total' => 0,
            ]);
            $total = 0;

            foreach ($requestedItems as $item) {
                $book = Book::query()
                    ->whereKey($item['book_id'])
                    ->where('is_active', true)
                    ->first();

                if ($book === null) {
                    throw ValidationException::withMessages([
                        'items' => 'Salah satu buku tidak lagi tersedia.',
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $stockUpdated = Book::query()
                    ->whereKey($book->id)
                    ->where('is_active', true)
                    ->where('stock', '>=', $quantity)
                    ->decrement('stock', $quantity);

                if ($stockUpdated !== 1) {
                    throw ValidationException::withMessages([
                        'items' => "Stok untuk {$book->title} tidak mencukupi.",
                    ]);
                }

                $sale->items()->create([
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'quantity' => $quantity,
                    'unit_price' => $book->price,
                ]);
                $total += $book->price * $quantity;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });

        return redirect()->route('sales.show', ['sale' => $sale->reference]);
    }

    public function show(Sale $sale): View
    {
        return view('sales.show', [
            'sale' => $sale->load('items'),
        ]);
    }

    public function index(): View
    {
        return view('admin.sales.index', [
            'sales' => Sale::query()->with('items')->latest()->paginate(20),
        ]);
    }

    public function markPaid(Sale $sale): RedirectResponse
    {
        $updated = Sale::query()
            ->whereKey($sale->id)
            ->where('payment_status', 'pending')
            ->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'updated_at' => now(),
            ]);

        if ($updated !== 1) {
            return back()->withErrors([
                'sale' => 'Hanya pesanan yang masih menunggu pembayaran yang dapat ditandai lunas.',
            ]);
        }

        return back()->with('status', 'Pembayaran berhasil dikonfirmasi.');
    }

    public function cancel(Sale $sale): RedirectResponse
    {
        $cancelled = DB::transaction(function () use ($sale): bool {
            $updated = Sale::query()
                ->whereKey($sale->id)
                ->where('payment_status', 'pending')
                ->update([
                    'payment_status' => 'cancelled',
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                return false;
            }

            $sale->load('items')->items->each(function ($item): void {
                Book::query()->whereKey($item->book_id)->increment('stock', $item->quantity);
            });

            return true;
        });

        if (! $cancelled) {
            return back()->withErrors([
                'sale' => 'Hanya pesanan yang masih menunggu pembayaran yang dapat dibatalkan.',
            ]);
        }

        return back()->with('status', 'Pesanan dibatalkan dan stok dikembalikan.');
    }
}
