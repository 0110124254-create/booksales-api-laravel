<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminBookController extends Controller
{
    public function index(): View
    {
        return view('admin.books.index', [
            'books' => Book::query()
                ->with('authorRecord')
                ->orderBy('title')
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.books.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['author'] = Author::query()->findOrFail($data['author_id'])->name;
        Book::query()->create($data);

        return redirect()
            ->route('admin.web-books.index')
            ->with('status', 'Buku berhasil ditambahkan.');
    }

    public function edit(Book $book): View
    {
        return view('admin.books.edit', [
            ...$this->formOptions(),
            'book' => $book,
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['author'] = Author::query()->findOrFail($data['author_id'])->name;
        $book->update($data);

        return redirect()
            ->route('admin.web-books.index')
            ->with('status', 'Buku berhasil diperbarui.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        if ($book->saleItems()->exists()) {
            return redirect()
                ->route('admin.web-books.index')
                ->with('error', 'Buku yang sudah tercatat dalam pesanan tidak dapat dihapus.');
        }

        $book->delete();

        return redirect()
            ->route('admin.web-books.index')
            ->with('status', 'Buku berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'authors' => Author::query()->orderBy('name')->get(),
            'genres' => Genre::query()->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author_id' => ['required', 'integer', 'exists:authors,id'],
            'genre' => ['required', 'string', Rule::exists('genres', 'name')],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
