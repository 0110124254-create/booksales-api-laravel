<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('orders.index', [
            'orders' => Sale::query()->whereBelongsTo($request->user())->with('items')->latest()->paginate(10),
        ]);
    }

    public function show(Request $request, string $reference): View
    {
        $order = Sale::query()->whereBelongsTo($request->user())->where('reference', $reference)->with('items')->firstOrFail();

        return view('orders.show', compact('order'));
    }
}
