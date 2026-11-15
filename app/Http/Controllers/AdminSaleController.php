<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSaleController extends Controller
{
    public function index(): View
    {
        return view('admin.sales.index', ['orders' => Sale::query()->with('user', 'items')->latest()->paginate(20)]);
    }

    public function show(Sale $sale): View
    {
        return view('admin.sales.show', ['order' => $sale->load('user', 'items')]);
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        $data = $request->validate(['fulfillment_status' => ['required', 'in:new,processing,shipped,completed']]);
        if ($sale->payment_status !== 'paid' && in_array($data['fulfillment_status'], ['processing', 'shipped', 'completed'], true)) {
            return back()->with('error', 'Pesanan baru dapat diproses setelah pembayaran terkonfirmasi.');
        }
        $sale->update($data);

        return back()->with('status', 'Status pengiriman diperbarui.');
    }
}
