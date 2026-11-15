<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\MidtransSnap;
use App\Services\RajaOngkir;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SaleController extends Controller
{
    public function store(Request $request, MidtransSnap $midtrans, RajaOngkir $rajaOngkir): JsonResponse
    {
        if (app()->environment('production') && ! $rajaOngkir->configured()) {
            return response()->json([
                'success' => false,
                'message' => 'Checkout belum aktif. Konfigurasikan API RajaOngkir dan ID asal toko terlebih dahulu.',
            ], 503);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'payment_method' => ['required', 'in:cash,midtrans'],
            'shipping_destination_id' => [$rajaOngkir->configured() ? 'required' : 'nullable', 'integer', 'min:1'],
            'shipping_destination' => [$rajaOngkir->configured() ? 'required' : 'nullable', 'string', 'max:255'],
            'shipping_courier' => [$rajaOngkir->configured() ? 'required' : 'nullable', 'string', 'max:50'],
            'shipping_service' => [$rajaOngkir->configured() ? 'required' : 'nullable', 'string', 'max:100'],
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

        if ($data['payment_method'] === 'midtrans' && ! $midtrans->configured()) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran online belum aktif. Isi MIDTRANS_SERVER_KEY pada konfigurasi server.',
            ], 503);
        }

        $shipping = [
            'shipping_destination_id' => null,
            'shipping_destination' => null,
            'shipping_courier' => null,
            'shipping_service' => null,
            'shipping_etd' => null,
            'shipping_cost' => 0,
        ];

        if ($rajaOngkir->configured()) {
            try {
                $quotes = $rajaOngkir->quotes((int) $data['shipping_destination_id'], $requestedItems->all());
            } catch (RuntimeException|ConnectionException $exception) {
                return response()->json(['success' => false, 'message' => $exception->getMessage()], 503);
            }

            $selectedQuote = collect($quotes)->first(fn (array $quote): bool => strcasecmp($quote['code'], $data['shipping_courier']) === 0
                && $quote['service'] === $data['shipping_service']
            );

            if ($selectedQuote === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pilihan kurir tidak tersedia lagi. Hitung ulang ongkos kirim sebelum memesan.',
                ], 422);
            }

            $shipping = [
                'shipping_destination_id' => (int) $data['shipping_destination_id'],
                'shipping_destination' => $data['shipping_destination'],
                'shipping_courier' => $selectedQuote['name'],
                'shipping_service' => $selectedQuote['service'],
                'shipping_etd' => $selectedQuote['etd'],
                'shipping_cost' => $selectedQuote['cost'],
            ];
        }

        try {
            $sale = DB::transaction(function () use ($request, $data, $requestedItems, $shipping): Sale {
                $sale = Sale::create([
                    'reference' => 'BOOK-'.Str::upper(Str::random(12)),
                    'user_id' => $request->user()->id,
                    'customer_name' => $data['customer_name'],
                    'customer_email' => $data['customer_email'] ?? null,
                    'customer_phone' => $data['customer_phone'],
                    'shipping_address' => $data['shipping_address'],
                    'payment_method' => $data['payment_method'],
                    'payment_status' => 'pending',
                    'total' => 0,
                    'subtotal' => 0,
                    ...$shipping,
                ]);
                $subtotal = 0;

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
                    $subtotal += $book->price * $quantity;
                }

                $sale->update([
                    'subtotal' => $subtotal,
                    'total' => $subtotal + $shipping['shipping_cost'],
                ]);

                return $sale->load('items');
            });
        } catch (ValidationException $exception) {
            return $this->validationError($exception->errors());
        }

        $paymentRedirectUrl = null;
        if ($sale->payment_method === 'midtrans') {
            try {
                $payment = $midtrans->createTransaction($sale);
                $sale->update([
                    'midtrans_token' => $payment['token'],
                    'midtrans_redirect_url' => $payment['redirect_url'],
                ]);
                $paymentRedirectUrl = $payment['redirect_url'];
            } catch (RuntimeException $exception) {
                $this->cancelPendingAndRestoreStock($sale);

                return response()->json(['success' => false, 'message' => $exception->getMessage()], 503);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Resource created successfully',
            'data' => [
                ...$sale->fresh()->load('items')->toArray(),
                'payment_redirect_url' => $paymentRedirectUrl,
            ],
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

    public function myIndex(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->sales()->with('items')->latest()->paginate(10),
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

        if ($sale->payment_method === 'midtrans') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan dengan pembayaran Midtrans tidak dapat diubah setelah transaksi pembayaran dibuat.',
                'data' => null,
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => ['sometimes', 'required', 'string', 'max:255'],
            'customer_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'customer_phone' => ['sometimes', 'required', 'string', 'max:30'],
            'payment_method' => ['sometimes', 'required', 'in:cash'],
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
                    $data['subtotal'] = $this->replaceItems($sale, $items);
                    $data['total'] = $data['subtotal'] + $sale->shipping_cost;
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

        if ($sale->payment_method === 'midtrans' && $sale->payment_status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Batalkan transaksi Midtrans dari dashboard merchant sebelum menghapus pesanan.',
                'data' => null,
            ], 409);
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

        if ($sale->payment_method === 'midtrans') {
            return response()->json([
                'success' => false,
                'message' => 'Status pembayaran Midtrans diperbarui otomatis melalui notifikasi terverifikasi.',
                'data' => null,
            ], 409);
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

        if ($sale->payment_method === 'midtrans' && $sale->payment_status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Batalkan transaksi Midtrans dari dashboard merchant agar status pembayaran tetap sinkron.',
                'data' => null,
            ], 409);
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

    private function cancelPendingAndRestoreStock(Sale $sale): void
    {
        DB::transaction(function () use ($sale): void {
            $lockedSale = Sale::with('items')->whereKey($sale->id)->lockForUpdate()->first();
            if ($lockedSale === null || $lockedSale->payment_status !== 'pending') {
                return;
            }

            $lockedSale->update(['payment_status' => 'cancelled']);
            foreach ($lockedSale->items as $item) {
                Book::query()->whereKey($item->book_id)->increment('stock', $item->quantity);
            }
        });
    }
}
