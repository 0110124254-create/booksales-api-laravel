<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $rules = [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ];

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $rules['start_date'][] = 'before_or_equal:end_date';
            $rules['end_date'][] = 'after_or_equal:start_date';
        }

        $filters = $request->validate($rules);

        $startDate = $filters['start_date'] ?? today()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? today()->toDateString();
        $paidSales = Sale::query()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        return view('admin.reports.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'paidCount' => (clone $paidSales)->count(),
            'paidRevenue' => (clone $paidSales)->sum('total'),
            'sales' => Sale::query()
                ->with('items')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }
}
