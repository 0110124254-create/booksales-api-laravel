<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminUserController extends Controller
{
    public function apiIndex(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => User::query()
                ->where('is_admin', false)
                ->withCount('sales')
                ->latest()
                ->paginate(20),
        ]);
    }

}
