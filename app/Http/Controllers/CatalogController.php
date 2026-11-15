<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'genre' => ['sometimes', 'nullable', 'string', 'exists:genres,name'],
            'q' => ['sometimes', 'nullable', 'string', 'max:150'],
        ]);
        $search = trim($validated['q'] ?? '');
        $selectedGenre = ! empty($validated['genre'])
            ? Genre::query()->where('name', $validated['genre'])->firstOrFail()
            : null;

        $booksQuery = Book::query()
                ->where('is_active', true)
                ->when($selectedGenre, fn ($query) => $query->where('genre', $selectedGenre->name))
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('author', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                }))
                ->orderByDesc('created_at')
                ->orderByDesc('id');
        $books = $booksQuery->get();

        return view('catalog.home', [
            'books' => $books,
            'featuredBooks' => Book::query()
                ->where('is_active', true)
                ->orderByRaw('CASE WHEN cover IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->take(4)
                ->get(),
            'genreCount' => Genre::query()->count(),
            'authorCount' => Author::query()->count(),
            'selectedGenre' => $selectedGenre,
            'search' => $search,
        ]);
    }

    public function show(Book $book): View
    {
        abort_unless($book->is_active, 404);

        return view('catalog.show', compact('book'));
    }
}
