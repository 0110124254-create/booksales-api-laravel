<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Genre;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function apiIndex(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'book_count' => Book::query()->count(),
                'genre_count' => Genre::query()->count(),
                'author_count' => Author::query()->count(),
                'sale_count' => Sale::query()->count(),
                'user_count' => User::query()->where('is_admin', false)->count(),
                'unread_message_count' => ContactMessage::query()->whereNull('read_at')->count(),
                'recent_sales' => Sale::query()->with('user')->latest()->take(5)->get(),
            ],
        ]);
    }

}
