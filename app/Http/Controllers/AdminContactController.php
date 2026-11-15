<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class AdminContactController extends Controller
{
    public function apiIndex(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ContactMessage::query()->with('user:id,name,email')->latest()->paginate(20),
        ]);
    }

    public function apiUpdate(ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage->update(['read_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Message marked as read', 'data' => $contactMessage->refresh()]);
    }
}
