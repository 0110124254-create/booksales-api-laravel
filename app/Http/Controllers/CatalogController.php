<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\View\View;

class CatalogController extends Controller
{
    public function index(): View
    {
        return view('catalog.home', [
            'books' => Book::query()
                ->where('is_active', true)
                ->orderBy('title')
                ->get(),
            'genreCount' => Genre::query()->count(),
            'authorCount' => Author::query()->count(),
        ]);
    }
}
