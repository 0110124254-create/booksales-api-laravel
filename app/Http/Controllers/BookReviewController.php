<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookReviewController extends Controller
{
    public function index(Request $request, int $book): JsonResponse
    {
        $bookModel = Book::query()->findOrFail($book);
        $reviews = $bookModel->reviews()->with('user:id,name')->latest()->paginate(10);
        $user = $request->user();
        $canReview = $user !== null
            && ! $user->is_admin
            && Sale::query()->whereBelongsTo($user)
                ->where('payment_status', 'paid')
                ->whereHas('items', fn ($items) => $items->where('book_id', $bookModel->id))
                ->exists()
            && ! $bookModel->reviews()->where('user_id', $user->id)->exists();

        return response()->json([
            'success' => true,
            'data' => $reviews->items(),
            'meta' => [
                'average_rating' => round((float) $bookModel->reviews()->avg('rating'), 1),
                'review_count' => $reviews->total(),
                'can_review' => $canReview,
                'current_page' => $reviews->currentPage(),
            ],
        ]);
    }

    public function store(Request $request, int $book): JsonResponse
    {
        $bookModel = Book::query()->findOrFail($book);
        $validator = Validator::make($request->all(), [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ulasan tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $sale = Sale::query()->whereBelongsTo($user)
            ->where('payment_status', 'paid')
            ->whereHas('items', fn ($items) => $items->where('book_id', $bookModel->id))
            ->latest()
            ->first();

        if ($sale === null) {
            return response()->json([
                'success' => false,
                'message' => 'Ulasan hanya dapat diberikan setelah buku ini dibeli dan pembayarannya terkonfirmasi.',
            ], 403);
        }

        if ($bookModel->reviews()->where('user_id', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Anda sudah memberikan ulasan untuk buku ini.'], 409);
        }

        $review = $bookModel->reviews()->create([
            'user_id' => $user->id,
            'sale_id' => $sale->id,
            ...$validator->validated(),
        ])->load('user:id,name');

        return response()->json(['success' => true, 'message' => 'Terima kasih atas ulasan Anda.', 'data' => $review], 201);
    }
}
