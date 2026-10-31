<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Sale;
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
            $sale = DB::transaction(function () use ($data, $requestedItems): Sale {
                $sale = Sale::create([
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

    public function show(string $reference): JsonResponse
    {
        $sale = Sale::with('items')->where('reference', $reference)->first();

        if ($sale === null) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'message' => 'Resource retrieved successfully',
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

    private function notFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Resource not found',
            'data' => null,
        ], 404);
    }
}
