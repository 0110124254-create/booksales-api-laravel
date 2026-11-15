<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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

        if ($request->hasFile('cover_upload')) {
            $data['cover'] = $this->storeCover($request->file('cover_upload'));
        }

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
        $data = $request->validate($this->rules($book));
        $data['author'] = Author::query()->findOrFail($data['author_id'])->name;

        if (($data['isbn'] ?? null) !== $book->isbn) {
            $data['cover'] = null;
        }

        $oldCover = $book->cover;
        if ($request->hasFile('cover_upload')) {
            $data['cover'] = $this->storeCover($request->file('cover_upload'));
        }

        $book->update($data);

        if ($oldCover !== $book->cover) {
            $this->deleteUploadedCover($oldCover);
        }

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
        $this->deleteUploadedCover($book->cover);

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
    private function rules(?Book $book = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author_id' => ['required', 'integer', 'exists:authors,id'],
            'genre' => ['required', 'string', Rule::exists('genres', 'name')],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book?->id)],
            'cover_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    private function storeCover(UploadedFile $file): string
    {
        $directory = public_path('images/covers/uploads');
        File::ensureDirectoryExists($directory);

        $filename = Str::uuid().'.'.$file->extension();
        $file->move($directory, $filename);

        return 'images/covers/uploads/'.$filename;
    }

    private function deleteUploadedCover(?string $cover): void
    {
        if ($cover !== null && preg_match('#^images/covers/uploads/[a-f0-9-]+\.(?:jpg|jpeg|png|webp)$#i', $cover)) {
            File::delete(public_path($cover));
        }
    }
}
