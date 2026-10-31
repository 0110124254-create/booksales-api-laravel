<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Get All Resource',
            'data' => Book::all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $this->bookData($validator->validated());
        $book = Book::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Resource created successfully',
            'data' => $book,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $book = Book::find($id);

        if ($book === null) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'message' => 'Resource retrieved successfully',
            'data' => $book,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $book = Book::find($id);

        if ($book === null) {
            return $this->notFound();
        }

        $validator = Validator::make($request->all(), $this->rules($request->isMethod('patch')));

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $book->update($this->bookData($validator->validated()));

        return response()->json([
            'success' => true,
            'message' => 'Resource updated successfully',
            'data' => $book,
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

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resource deleted successfully',
            'data' => null,
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(bool $partial = false): array
    {
        return [
            'title' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'author_id' => [
                ...($partial ? ['sometimes'] : []),
                'nullable',
                'integer',
                'exists:authors,id',
            ],
            'author' => [
                ...($partial ? ['sometimes'] : ['required_without:author_id']),
                'nullable',
                'string',
                'max:255',
            ],
            'genre' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'string'],
            'price' => [$partial ? 'sometimes' : 'required', 'integer', 'min:0'],
            'stock' => [$partial ? 'sometimes' : 'required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function bookData(array $data): array
    {
        if (isset($data['author_id'])) {
            $data['author'] = Author::findOrFail($data['author_id'])->name;
        } elseif (array_key_exists('author', $data)) {
            $data['author_id'] = null;
        }

        return $data;
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
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
        return response()->json([
            'success' => false,
            'message' => 'Resource not found',
            'data' => null,
        ], 404);
    }
}
