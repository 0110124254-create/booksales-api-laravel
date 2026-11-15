<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GenreController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Get All Resource',
            'data' => Genre::query()->withCount('books')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $genre = Genre::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Resource created successfully',
            'data' => $genre->loadCount('books'),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $genre = Genre::find($id);

        if ($genre === null) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'message' => 'Resource retrieved successfully',
            'data' => $genre,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $genre = Genre::find($id);

        if ($genre === null) {
            return $this->notFound();
        }

        $validator = Validator::make(
            $request->all(),
            $this->rules($genre->id, $request->isMethod('patch')),
        );

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $genre->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Resource updated successfully',
            'data' => $genre->refresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $genre = Genre::find($id);

        if ($genre === null) {
            return $this->notFound();
        }

        if (Book::query()->where('genre_id', $genre->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Genres assigned to books cannot be deleted',
                'data' => null,
            ], 409);
        }

        $genre->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resource deleted successfully',
            'data' => null,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?int $ignoreId = null, bool $partial = false): array
    {
        return [
            'name' => [
                $partial ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('genres', 'name')->ignore($ignoreId),
            ],
            'description' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'string'],
        ];
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
