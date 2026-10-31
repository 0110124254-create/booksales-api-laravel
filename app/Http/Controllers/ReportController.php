<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rules = [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ];

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $rules['start_date'][] = 'before_or_equal:end_date';
            $rules['end_date'][] = 'after_or_equal:start_date';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $validator->errors(),
            ], 422);
        }

        $filters = $validator->validated();
        $startDate = $filters['start_date'] ?? today()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? today()->toDateString();
        $paidSales = Sale::query()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        return response()->json([
            'success' => true,
            'message' => 'Sales report retrieved successfully',
            'data' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'paid_count' => (clone $paidSales)->count(),
                'paid_revenue' => (clone $paidSales)->sum('total'),
                'sales' => Sale::with('items')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->latest()
                    ->get(),
            ],
        ]);
    }
}
