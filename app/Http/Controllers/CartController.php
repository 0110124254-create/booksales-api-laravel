<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', []);
        $books = Book::query()->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $items = collect($cart)->map(function (int $quantity, string $id) use ($books): ?array {
            $book = $books->get($id);
            return $book ? ['book' => $book, 'quantity' => $quantity, 'subtotal' => $book->price * $quantity] : null;
        })->filter()->values();

        return view('cart.index', ['items' => $items, 'total' => $items->sum('subtotal')]);
    }

    public function store(Request $request, Book $book): RedirectResponse
    {
        if (! $book->is_active || $book->stock < 1) {
            return back()->with('error', 'Buku sedang tidak tersedia.');
        }

        $quantity = $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:100']])['quantity'] ?? 1;
        $cart = $request->session()->get('cart', []);
        $cart[$book->id] = min($book->stock, ($cart[$book->id] ?? 0) + (int) $quantity);
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('status', 'Buku ditambahkan ke keranjang.');
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $quantity = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:100']])['quantity'];
        if (! $book->is_active || $quantity > $book->stock) {
            return back()->with('error', 'Jumlah melebihi stok buku yang tersedia.');
        }

        $cart = $request->session()->get('cart', []);
        if (array_key_exists($book->id, $cart)) {
            $cart[$book->id] = (int) $quantity;
            $request->session()->put('cart', $cart);
        }

        return back()->with('status', 'Keranjang diperbarui.');
    }

    public function destroy(Request $request, Book $book): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$book->id]);
        $request->session()->put('cart', $cart);

        return back()->with('status', 'Buku dihapus dari keranjang.');
    }
}
