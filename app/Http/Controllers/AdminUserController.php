<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', ['users' => User::query()->where('is_admin', false)->withCount('sales')->latest()->paginate(20)]);
    }
}
