<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminGenreController extends Controller
{
    public function index(): View
    {
        return view('admin.genres.index', ['genres' => Genre::query()->withCount('books')->orderBy('name')->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.genres.form', ['genre' => new Genre]);
    }

    public function store(Request $request): RedirectResponse
    {
        Genre::create($request->validate($this->rules()));

        return redirect()->route('admin.web-genres.index')->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Genre $genre): View
    {
        return view('admin.genres.form', compact('genre'));
    }

    public function update(Request $request, Genre $genre): RedirectResponse
    {
        $data = $request->validate($this->rules($genre));
        if ($genre->name !== $data['name']) {
            Book::query()->where('genre', $genre->name)->update(['genre' => $data['name']]);
        }
        $genre->update($data);

        return redirect()->route('admin.web-genres.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        if (Book::query()->where('genre', $genre->name)->exists()) {
            return back()->with('error', 'Kategori masih dipakai buku. Ubah atau hapus buku terkait terlebih dahulu.');
        }
        $genre->delete();

        return back()->with('status', 'Kategori berhasil dihapus.');
    }

    private function rules(?Genre $genre = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('genres', 'name')->ignore($genre?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
