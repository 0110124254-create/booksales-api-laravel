<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminContactController extends Controller
{
    public function apiIndex(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ContactMessage::query()->with('user:id,name,email')->latest()->paginate(20),
        ]);
    }

    public function index(): View
    {
        return view('admin.contacts.index', ['messages' => ContactMessage::query()->latest()->paginate(20)]);
    }

    public function update(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['read_at' => now()]);

        return back()->with('status', 'Pesan ditandai sudah dibaca.');
    }

    public function apiUpdate(ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage->update(['read_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Message marked as read', 'data' => $contactMessage->refresh()]);
    }
}
