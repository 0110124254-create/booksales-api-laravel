<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSaleController extends Controller
{
    public function apiIndex(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Sale::query()->with(['user:id,name,email', 'items'])->latest()->paginate(20),
        ]);
    }

    public function apiUpdate(Request $request, Sale $sale): JsonResponse
    {
        $data = $request->validate([
            'fulfillment_status' => ['required', 'in:new,processing,shipped,completed'],
        ]);

        if ($data === []) {
            return response()->json(['success' => false, 'message' => 'No status provided'], 422);
        }

        if (isset($data['fulfillment_status'])
            && $sale->payment_status !== 'paid'
            && in_array($data['fulfillment_status'], ['processing', 'shipped', 'completed'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan baru dapat diproses setelah pembayaran terkonfirmasi.',
                'errors' => ['fulfillment_status' => ['Pesanan belum dibayar.']],
            ], 422);
        }

        $sale->update($data);

        return response()->json(['success' => true, 'message' => 'Order status updated', 'data' => $sale->refresh()->load(['user', 'items'])]);
    }
}
