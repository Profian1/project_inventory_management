<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the dashboard: today's sales/profit, charts, low stock,
     * recent sales, top products and the outstanding debts summary.
     */
    public function __invoke(): View
    {
        // The heavy aggregate queries are cached for 5 minutes so the
        // dashboard stays fast even during a busy trading day.
        $stats = Cache::remember('dashboard.stats', now()->addMinutes(5), function () {
            return [
                'todaySales' => (float) Sale::today()->sum('total'),
                'todayProfit' => (float) SaleItem::query()
                    ->whereHas('sale', fn ($query) => $query->whereDate('created_at', today()))
                    ->sum(DB::raw('(unit_price - buying_price) * quantity')),
                'todayTransactions' => Sale::today()->count(),
                'lowStockCount' => Product::where('is_active', true)->lowStock()->count(),
                'sales7Days' => $this->salesTrend(7),
                'profit30Days' => $this->profitTrend(30),
                'topProducts' => SaleItem::query()
                    ->whereHas('sale', fn ($query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()]))
                    ->selectRaw('product_name, SUM(quantity) as qty_sold, SUM(subtotal) as revenue')
                    ->groupBy('product_name')
                    ->orderByDesc('qty_sold')
                    ->take(5)
                    ->get()
                    ->toArray(),
                'totalDebt' => (float) Debt::unpaid()->sum('balance'),
                'debtorsCount' => Debt::unpaid()->distinct()->count('customer_id'),
            ];
        });

        // Small, fresh queries — a handful of rows doesn't need caching.
        $lowStockProducts = Product::where('is_active', true)
            ->lowStock()
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        $recentSales = Sale::with('customer')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', array_merge($stats, [
            'lowStockProducts' => $lowStockProducts,
            'recentSales' => $recentSales,
        ]));
    }

    /**
     * Build "last N days" sales totals (labels + values) for the bar chart.
     */
    private function salesTrend(int $days): array
    {
        $dates = collect(range($days - 1, 0))->map(fn ($i) => now()->subDays($i));

        $totals = Sale::query()
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('day')
            ->pluck('total', 'day');

        return [
            'labels' => $dates->map(fn ($date) => $date->format('D'))->all(),
            'values' => $dates->map(fn ($date) => (float) ($totals[$date->format('Y-m-d')] ?? 0))->all(),
        ];
    }

    /**
     * Build "last N days" profit totals (labels + values) for the line chart.
     */
    private function profitTrend(int $days): array
    {
        $dates = collect(range($days - 1, 0))->map(fn ($i) => now()->subDays($i));

        $profits = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->selectRaw('DATE(sales.created_at) as day, SUM((sale_items.unit_price - sale_items.buying_price) * sale_items.quantity) as profit')
            ->groupBy('day')
            ->pluck('profit', 'day');

        return [
            'labels' => $dates->map(fn ($date) => $date->format('M j'))->all(),
            'values' => $dates->map(fn ($date) => (float) ($profits[$date->format('Y-m-d')] ?? 0))->all(),
        ];
    }
}
