<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'payment_method' => ['required', 'in:cash,bank_transfer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.book_id' => ['required', 'integer', 'distinct', 'exists:books,id'],
            'items.*.quantity' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $requestedItems = collect($data['items'])
            ->filter(fn (array $item): bool => $item['quantity'] > 0);

        if ($requestedItems->isEmpty()) {
            return $this->validationError([
                'items' => ['Pilih setidaknya satu buku dengan jumlah lebih dari nol.'],
            ]);
        }

        try {
            $sale = DB::transaction(function () use ($request, $data, $requestedItems): Sale {
                $sale = Sale::create([
                    'reference' => 'BOOK-'.Str::upper(Str::random(12)),
                    'user_id' => $request->user()->id,
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

                return $sale->load('items');
            });
        } catch (ValidationException $exception) {
            return $this->validationError($exception->errors());
        }

        return response()->json([
            'success' => true,
            'message' => 'Resource created successfully',
            'data' => $sale,
        ], 201);
    }

    public function show(Request $request, string $reference): JsonResponse
    {
        $sale = Sale::with('items')
            ->whereBelongsTo($request->user())
            ->where('reference', $reference)
            ->first();

        if ($sale === null) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'message' => 'Resource retrieved successfully',
            'data' => $sale,
        ]);
    }

    public function update(Request $request, string $reference): JsonResponse
    {
        $sale = Sale::query()
            ->whereBelongsTo($request->user())
            ->where('reference', $reference)
            ->first();

        if ($sale === null) {
            return $this->notFound();
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => ['sometimes', 'required', 'string', 'max:255'],
            'customer_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'customer_phone' => ['sometimes', 'required', 'string', 'max:30'],
            'payment_method' => ['sometimes', 'required', 'in:cash,bank_transfer'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.book_id' => ['required_with:items', 'integer', 'distinct', 'exists:books,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:0', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        if ($data === []) {
            return $this->validationError([
                'data' => ['Setidaknya satu data transaksi harus diperbarui.'],
            ]);
        }

        $items = $data['items'] ?? null;
        unset($data['items']);

        try {
            $sale = DB::transaction(function () use ($request, $sale, $data, $items): Sale {
                $sale = Sale::query()
                    ->whereBelongsTo($request->user())
                    ->whereKey($sale->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($sale->payment_status !== 'pending') {
                    throw ValidationException::withMessages([
                        'sale' => ['Hanya transaksi pending yang dapat diperbarui.'],
                    ]);
                }

                if ($items !== null) {
                    $data['total'] = $this->replaceItems($sale, $items);
                }

                $sale->update($data);

                return $sale->load('items');
            });
        } catch (ValidationException $exception) {
            if (isset($exception->errors()['sale'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction cannot be updated',
                    'data' => null,
                    'errors' => $exception->errors(),
                ], 409);
            }

            return $this->validationError($exception->errors());
        }

        return response()->json([
            'success' => true,
            'message' => 'Resource updated successfully',
            'data' => $sale,
        ]);
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Get All Resource',
            'data' => Sale::with('items')->latest()->get(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $sale = Sale::with('items')->find($id);

        if ($sale === null) {
            return $this->notFound();
        }

        DB::transaction(function () use ($sale): void {
            $sale = Sale::with('items')
                ->whereKey($sale->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sale->payment_status === 'pending') {
                $sale->items->each(function (SaleItem $item): void {
                    Book::query()->whereKey($item->book_id)->increment('stock', $item->quantity);
                });
            }

            $sale->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Resource deleted successfully',
            'data' => null,
        ]);
    }

    public function markPaid(int $id): JsonResponse
    {
        $sale = Sale::find($id);

        if ($sale === null) {
            return $this->notFound();
        }

        $updated = Sale::query()
            ->whereKey($sale->id)
            ->where('payment_status', 'pending')
            ->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'updated_at' => now(),
            ]);

        if ($updated !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be marked as paid',
                'data' => null,
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed successfully',
            'data' => $sale->refresh(),
        ]);
    }

    public function cancel(int $id): JsonResponse
    {
        $sale = Sale::find($id);

        if ($sale === null) {
            return $this->notFound();
        }

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
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be cancelled',
                'data' => null,
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled and stock restored',
            'data' => $sale->refresh()->load('items'),
        ]);
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    private function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'data' => null,
            'errors' => $errors,
        ], 422);
    }

    /**
     * @param  array<int, array{book_id: int, quantity: int}>  $requestedItems
     */
    private function replaceItems(Sale $sale, array $requestedItems): int
    {
        $existingItems = $sale->items()->get();
        $bookIds = $existingItems->pluck('book_id')
            ->merge(collect($requestedItems)->pluck('book_id'))
            ->unique()
            ->sort()
            ->values();
        $books = Book::query()
            ->whereIn('id', $bookIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($existingItems as $existingItem) {
            $books->get($existingItem->book_id)?->increment('stock', $existingItem->quantity);
        }

        $sale->items()->delete();
        $total = 0;
        $activeItems = collect($requestedItems)
            ->filter(fn (array $item): bool => $item['quantity'] > 0);

        if ($activeItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['Pilih setidaknya satu buku dengan jumlah lebih dari nol.'],
            ]);
        }

        foreach ($activeItems as $item) {
            $book = $books->get($item['book_id']);

            if ($book === null || ! $book->is_active) {
                throw ValidationException::withMessages([
                    'items' => ['Salah satu buku tidak lagi tersedia.'],
                ]);
            }

            $quantity = (int) $item['quantity'];
            if ($book->stock < $quantity) {
                throw ValidationException::withMessages([
                    'items' => ["Stok untuk {$book->title} tidak mencukupi."],
                ]);
            }

            $book->decrement('stock', $quantity);
            $sale->items()->create([
                'book_id' => $book->id,
                'book_title' => $book->title,
                'quantity' => $quantity,
                'unit_price' => $book->price,
            ]);
            $total += $book->price * $quantity;
        }

        return $total;
    }

    private function notFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Resource not found',
            'data' => null,
        ], 404);
    }
}
