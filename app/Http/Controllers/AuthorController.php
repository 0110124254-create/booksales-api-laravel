<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthorController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Get All Resource',
            'data' => Author::withCount('books')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $author = Author::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Resource created successfully',
            'data' => $author,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $author = Author::withCount('books')->find($id);

        if ($author === null) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'message' => 'Resource retrieved successfully',
            'data' => $author,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $author = Author::find($id);

        if ($author === null) {
            return $this->notFound();
        }

        $validator = Validator::make(
            $request->all(),
            $this->rules($author->id, $request->isMethod('patch')),
        );

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        DB::transaction(function () use ($author, $data): void {
            $oldName = $author->name;
            $author->update($data);

            if (isset($data['name']) && $oldName !== $author->name) {
                Book::query()
                    ->where('author_id', $author->id)
                    ->update(['author' => $author->name]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Resource updated successfully',
            'data' => $author->refresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $author = Author::find($id);

        if ($author === null) {
            return $this->notFound();
        }

        if ($author->books()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Authors assigned to books cannot be deleted',
                'data' => null,
            ], 409);
        }

        $author->delete();

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
                Rule::unique('authors', 'name')->ignore($ignoreId),
            ],
            'nationality' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'string', 'max:255'],
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
