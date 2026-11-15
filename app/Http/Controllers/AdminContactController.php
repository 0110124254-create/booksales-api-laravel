<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminContactController extends Controller
{
    public function index(): View
    {
        return view('admin.contacts.index', ['messages' => ContactMessage::query()->latest()->paginate(20)]);
    }

    public function update(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['read_at' => now()]);

        return back()->with('status', 'Pesan ditandai sudah dibaca.');
    }
}
