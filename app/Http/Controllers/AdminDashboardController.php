<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Genre;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'bookCount' => Book::query()->count(),
            'genreCount' => Genre::query()->count(),
            'authorCount' => Author::query()->count(),
            'saleCount' => Sale::query()->count(),
            'userCount' => User::query()->where('is_admin', false)->count(),
            'unreadMessageCount' => ContactMessage::query()->whereNull('read_at')->count(),
        ]);
    }
}
