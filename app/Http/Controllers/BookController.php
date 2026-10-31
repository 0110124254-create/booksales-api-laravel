<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function catalog(): View
    {
        return view('catalog.home', [
            'books' => Book::query()
                ->with('authorRecord')
                ->where('is_active', true)
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function index(): View
    {
        return view('admin.books.index', [
            'books' => Book::query()
                ->with('authorRecord')
                ->orderBy('title')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.books.form', [
            'book' => new Book,
            'authors' => Author::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Book::query()->create($this->validatedBook($request));

        return redirect()->route('admin.books.index')->with('status', 'Buku berhasil ditambahkan.');
    }

    public function edit(Book $book): View
    {
        return view('admin.books.form', [
            'book' => $book,
            'authors' => Author::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $book->update($this->validatedBook($request));

        return redirect()->route('admin.books.index')->with('status', 'Buku berhasil diperbarui.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        if ($book->saleItems()->exists()) {
            return back()->withErrors([
                'book' => 'Buku yang sudah pernah dipesan tidak dapat dihapus.',
            ]);
        }

        $book->delete();

        return redirect()->route('admin.books.index')->with('status', 'Buku berhasil dihapus.');
    }

    /**
     * @return array{title: string, author_id: int, author: string, genre: string, description: ?string, price: int, stock: int, is_active: bool}
     */
    private function validatedBook(Request $request): array
    {
        $book = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author_id' => ['required', 'integer', 'exists:authors,id'],
            'genre' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'integer', 'min:1000', 'max:1000000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $book['author_id'] = (int) $book['author_id'];
        $book['author'] = Author::query()->findOrFail($book['author_id'])->name;
        $book['price'] = (int) $book['price'];
        $book['stock'] = (int) $book['stock'];
        $book['is_active'] = $request->boolean('is_active');

        return $book;
    }
}
