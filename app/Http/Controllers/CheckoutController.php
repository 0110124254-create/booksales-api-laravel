<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Sale;
use App\Services\MidtransSnap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function create(Request $request, MidtransSnap $midtrans): View|RedirectResponse
    {
        if (! $midtrans->configured()) {
            return redirect()->route('cart.index')->with('error', 'Pembayaran online belum dikonfigurasi. Admin perlu mengisi kunci Midtrans di .env.');
        }

        $cart = $request->session()->get('cart', []);
        if ($cart === []) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $books = Book::query()->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $items = collect($cart)->map(fn (int $quantity, string $id): ?array => $books->has($id)
            ? ['book' => $books->get($id), 'quantity' => $quantity]
            : null)->filter()->values();

        return view('checkout.create', ['items' => $items, 'total' => $items->sum(fn ($item) => $item['book']->price * $item['quantity'])]);
    }

    public function store(Request $request, MidtransSnap $midtrans): RedirectResponse
    {
        if (! $midtrans->configured()) {
            return redirect()->route('cart.index')->with('error', 'Pembayaran online belum dikonfigurasi.');
        }

        $data = $request->validate([
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:2000'],
        ]);
        $cart = $request->session()->get('cart', []);
        if ($cart === []) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        try {
            $sale = DB::transaction(function () use ($request, $cart, $data): Sale {
                $sale = Sale::create([
                    'reference' => 'RB-'.Str::upper(Str::random(12)),
                    'user_id' => $request->user()->id,
                    'customer_name' => $request->user()->name,
                    'customer_email' => $request->user()->email,
                    'customer_phone' => $data['customer_phone'],
                    'shipping_address' => $data['shipping_address'],
                    'payment_method' => 'midtrans',
                    'payment_status' => 'pending',
                    'fulfillment_status' => 'new',
                    'total' => 0,
                ]);
                $total = 0;

                foreach ($cart as $bookId => $quantity) {
                    $book = Book::query()->whereKey($bookId)->lockForUpdate()->first();
                    $quantity = (int) $quantity;
                    if ($book === null || ! $book->is_active || $quantity < 1 || $book->stock < $quantity) {
                        throw new RuntimeException('Stok salah satu buku berubah. Perbarui keranjang lalu coba lagi.');
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
                $sale->update(['total' => $total]);

                return $sale->load('items');
            });

            $payment = $midtrans->createTransaction($sale);
            $sale->update([
                'midtrans_token' => $payment['token'],
                'midtrans_redirect_url' => $payment['redirect_url'],
            ]);
        } catch (RuntimeException $exception) {
            if (isset($sale)) {
                DB::transaction(function () use ($sale): void {
                    $sale->load('items')->update(['payment_status' => 'cancelled']);
                    foreach ($sale->items as $item) {
                        Book::query()->whereKey($item->book_id)->increment('stock', $item->quantity);
                    }
                });
            }

            return back()->withInput()->with('error', $exception->getMessage());
        }

        $request->session()->forget('cart');

        return redirect()->away($payment['redirect_url']);
    }
}
