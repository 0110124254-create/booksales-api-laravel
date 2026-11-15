<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'q' => ['nullable', 'string', 'max:255'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $query = Book::query()->with(['authorRecord', 'genreRecord']);

        if (! $request->user()?->is_admin) {
            $query->where('is_active', true);
        }

        if (! empty($filters['q'])) {
            $search = trim($filters['q']);
            $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('isbn', 'like', '%'.$search.'%')
                    ->orWhere('author', 'like', '%'.$search.'%')
                    ->orWhereHas('authorRecord', fn ($author) => $author->where('name', 'like', '%'.$search.'%'));
            });
        }

        if (! empty($filters['genre_id'])) {
            $query->where('genre_id', $filters['genre_id']);
        }

        $books = $query->orderBy('title')->paginate((int) ($filters['per_page'] ?? 12));

        return response()->json([
            'success' => true,
            'message' => 'Get All Resource',
            'data' => $books->items(),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $this->bookData($validator->validated());
        if ($request->hasFile('cover_upload')) {
            $data['cover'] = $this->storeCover($request->file('cover_upload'));
        }

        $book = Book::query()->create($data)->load(['authorRecord', 'genreRecord']);

        return response()->json([
            'success' => true,
            'message' => 'Book created successfully',
            'data' => $book,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $book = Book::query()->with(['authorRecord', 'genreRecord'])->find($id);

        if ($book === null || (! $book->is_active && ! request()->user()?->is_admin)) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'message' => 'Book retrieved successfully',
            'data' => $book,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $book = Book::find($id);
        if ($book === null) {
            return $this->notFound();
        }

        $validator = Validator::make($request->all(), $this->rules($request->isMethod('patch'), $book));
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $this->bookData($validator->validated());
        if ($request->hasFile('cover_upload')) {
            $oldCover = $book->cover;
            $data['cover'] = $this->storeCover($request->file('cover_upload'));
            $this->deleteUploadedCover($oldCover);
        }

        $book->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Book updated successfully',
            'data' => $book->refresh()->load(['authorRecord', 'genreRecord']),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $book = Book::find($id);
        if ($book === null) {
            return $this->notFound();
        }

        if ($book->saleItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Books with existing orders cannot be deleted',
                'data' => null,
            ], 409);
        }

        $cover = $book->cover;
        $book->delete();
        $this->deleteUploadedCover($cover);

        return response()->json([
            'success' => true,
            'message' => 'Book deleted successfully',
            'data' => null,
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(bool $partial = false, ?Book $book = null): array
    {
        return [
            'title' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'author_id' => [$partial ? 'sometimes' : 'required_without:author', 'nullable', 'integer', 'exists:authors,id'],
            'author' => [$partial ? 'sometimes' : 'required_without:author_id', 'nullable', 'string', 'max:255'],
            'genre_id' => [$partial ? 'sometimes' : 'required_without:genre', 'nullable', 'integer', 'exists:genres,id'],
            'genre' => [$partial ? 'sometimes' : 'required_without:genre_id', 'nullable', 'string', Rule::exists('genres', 'name')],
            'description' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'string'],
            'price' => [$partial ? 'sometimes' : 'required', 'integer', 'min:0'],
            'stock' => [$partial ? 'sometimes' : 'required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book?->id)],
            'cover_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function bookData(array $data): array
    {
        if (isset($data['author_id'])) {
            $data['author'] = Author::query()->findOrFail($data['author_id'])->name;
        } elseif (isset($data['author'])) {
            $data['author_id'] = Author::query()->where('name', $data['author'])->value('id');
        }

        if (isset($data['genre_id'])) {
            $genre = Genre::query()->findOrFail($data['genre_id']);
            $data['genre'] = $genre->name;
        } elseif (isset($data['genre'])) {
            $genre = Genre::query()->where('name', $data['genre'])->firstOrFail();
            $data['genre_id'] = $genre->id;
        }

        unset($data['cover_upload']);

        return $data;
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

    /** @param array<string, array<int, string>> $errors */
    private function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'data' => null,
            'errors' => $errors,
        ], 422);
    }

    private function notFound(): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Resource not found', 'data' => null], 404);
    }
}
