<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Reports landing page.
     */
    public function index()
    {
        return view('reports.index');
    }

    /*
    |--------------------------------------------------------------------------
    | Report 1: Daily Sales
    |--------------------------------------------------------------------------
    */

    public function dailySales(Request $request)
    {
        return view('reports.daily-sales', $this->dailySalesData($request));
    }

    public function exportDailySales(Request $request): StreamedResponse
    {
        $data = $this->dailySalesData($request);

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', '# Sales', 'Gross Sales', 'Profit', 'VAT']);
            foreach ($data['daily'] as $row) {
                fputcsv($handle, [$row['date'], $row['count'], $row['gross'], $row['profit'], $row['vat']]);
            }
            fclose($handle);
        }, "daily-sales-{$data['from']}-{$data['to']}.csv");
    }

    private function dailySalesData(Request $request): array
    {
        $from = $request->get('from', today()->format('Y-m-d'));
        $to = $request->get('to', today()->format('Y-m-d'));
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();

        return Cache::remember("reports.daily.{$from}.{$to}", 600, function () use ($from, $to, $start, $end) {
            $sales = Sale::whereBetween('created_at', [$start, $end])->get();

            $profitByDate = SaleItem::query()
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->whereBetween('sales.created_at', [$start, $end])
                ->selectRaw('DATE(sales.created_at) as day, SUM((sale_items.unit_price - sale_items.buying_price) * sale_items.quantity) as profit')
                ->groupBy('day')
                ->pluck('profit', 'day');

            $daily = $sales->groupBy(fn ($s) => $s->created_at->format('Y-m-d'))
                ->map(function ($group) use ($profitByDate) {
                    $date = $group->first()->created_at->format('Y-m-d');

                    return [
                        'date' => $date,
                        'count' => $group->count(),
                        'gross' => (float) $group->sum('total'),
                        'profit' => (float) ($profitByDate[$date] ?? 0),
                        'vat' => (float) $group->sum('vat_amount'),
                    ];
                })
                ->sortBy('date')
                ->values();

            $byPayment = $sales->groupBy('payment_method')->map(fn ($g) => (float) $g->sum('total'))->toArray();

            return [
                'from' => $from,
                'to' => $to,
                'totalSales' => (float) $sales->sum('total'),
                'transactionCount' => $sales->count(),
                'avgSale' => $sales->count() ? (float) $sales->sum('total') / $sales->count() : 0,
                'totalProfit' => (float) $profitByDate->sum(),
                'daily' => $daily,
                'byPayment' => $byPayment,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Report 2: Low Stock / Inventory
    |--------------------------------------------------------------------------
    */

    public function lowStock(Request $request)
    {
        $data = $this->lowStockData($request);

        return view('reports.low-stock', array_merge($data, [
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]));
    }

    public function exportLowStock(Request $request): StreamedResponse
    {
        $data = $this->lowStockData($request);

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Product', 'Category', 'Stock', 'Reorder Level', 'Status', 'Buying Value', 'Selling Value']);
            foreach ($data['products'] as $p) {
                $status = $p['stock_quantity'] == 0 ? 'Out' : ($p['stock_quantity'] <= $p['reorder_level'] ? 'Low' : 'In');
                fputcsv($handle, [$p['name'], $p['category'], $p['stock_quantity'], $p['reorder_level'], $status, $p['buying_value'], $p['selling_value']]);
            }
            fclose($handle);
        }, 'low-stock-' . now()->format('Y-m-d') . '.csv');
    }

    private function lowStockData(Request $request): array
    {
        $categoryId = $request->get('category_id');
        $supplierId = $request->get('supplier_id');

        return Cache::remember("reports.lowstock.{$categoryId}.{$supplierId}", 600, function () use ($categoryId, $supplierId) {
            $products = Product::query()
                ->with('category')
                ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
                ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
                ->get();

            $rows = $products->map(fn ($p) => [
                'name' => $p->name,
                'category' => $p->category?->name ?? '—',
                'stock_quantity' => (int) $p->stock_quantity,
                'reorder_level' => (int) $p->reorder_level,
                'buying_value' => $p->stock_quantity * (float) $p->buying_price,
                'selling_value' => $p->stock_quantity * (float) $p->selling_price,
            ]);

            $byCategory = $products->groupBy(fn ($p) => $p->category?->name ?? 'Uncategorized')
                ->map(fn ($g) => (float) $g->sum(fn ($p) => $p->stock_quantity * (float) $p->selling_price))
                ->sortDesc()
                ->toArray();

            return [
                'products' => $rows->toArray(),
                'totalProducts' => $products->count(),
                'totalBuyingValue' => (float) $rows->sum('buying_value'),
                'totalSellingValue' => (float) $rows->sum('selling_value'),
                'lowStockCount' => $products->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 'reorder_level')->count(),
                'outOfStockCount' => $products->where('stock_quantity', 0)->count(),
                'byCategory' => $byCategory,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Report 3: Profit & Margin
    |--------------------------------------------------------------------------
    */

    public function profitMargin(Request $request)
    {
        $data = $this->profitMarginData($request);

        return view('reports.profit-margin', array_merge($data, [
            'categories' => Category::orderBy('name')->get(),
        ]));
    }

    public function exportProfitMargin(Request $request): StreamedResponse
    {
        $data = $this->profitMarginData($request);

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Product', 'Qty Sold', 'Revenue', 'Cost', 'Profit', 'Margin %']);
            foreach ($data['items'] as $row) {
                fputcsv($handle, [$row['product'], $row['qty'], $row['revenue'], $row['cost'], $row['profit'], $row['margin']]);
            }
            fclose($handle);
        }, "profit-margin-{$data['from']}-{$data['to']}.csv");
    }

    private function profitMarginData(Request $request): array
    {
        $from = $request->get('from', today()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', today()->format('Y-m-d'));
        $categoryId = $request->get('category_id');
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();

        return Cache::remember("reports.profit.{$from}.{$to}.{$categoryId}", 600, function () use ($from, $to, $categoryId, $start, $end) {
            $rows = SaleItem::query()
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->whereBetween('sales.created_at', [$start, $end])
                ->when($categoryId, fn ($q) => $q->whereIn('sale_items.product_id', Product::where('category_id', $categoryId)->select('id')))
                ->selectRaw('sale_items.product_name as product, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as revenue, SUM(sale_items.buying_price * sale_items.quantity) as cost')
                ->groupBy('sale_items.product_name')
                ->orderByDesc(DB::raw('SUM(sale_items.subtotal) - SUM(sale_items.buying_price * sale_items.quantity)'))
                ->get();

            $items = $rows->map(function ($r) {
                $revenue = (float) $r->revenue;
                $cost = (float) $r->cost;
                $profit = $revenue - $cost;

                return [
                    'product' => $r->product,
                    'qty' => (int) $r->qty,
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $profit,
                    'margin' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
                ];
            })->toArray();

            $totalRevenue = (float) array_sum(array_column($items, 'revenue'));
            $totalCost = (float) array_sum(array_column($items, 'cost'));
            $totalProfit = $totalRevenue - $totalCost;

            return [
                'from' => $from,
                'to' => $to,
                'items' => $items,
                'totalRevenue' => $totalRevenue,
                'totalCost' => $totalCost,
                'totalProfit' => $totalProfit,
                'avgMargin' => $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0,
                'top10' => array_slice($items, 0, 10),
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Report 5 (bonus): Sales by Cashier
    |--------------------------------------------------------------------------
    */

    public function cashier(Request $request)
    {
        return view('reports.cashier', $this->cashierData($request));
    }

    public function exportCashier(Request $request): StreamedResponse
    {
        $data = $this->cashierData($request);

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Cashier', '# Sales', 'Total Sales', 'Avg Sale']);
            foreach ($data['rows'] as $row) {
                fputcsv($handle, [$row['name'], $row['count'], $row['total'], $row['avg']]);
            }
            fclose($handle);
        }, "sales-by-cashier-{$data['from']}-{$data['to']}.csv");
    }

    private function cashierData(Request $request): array
    {
        $from = $request->get('from', today()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', today()->format('Y-m-d'));
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();

        return Cache::remember("reports.cashier.{$from}.{$to}", 600, function () use ($from, $to, $start, $end) {
            $rows = Sale::query()
                ->join('users', 'users.id', '=', 'sales.user_id')
                ->whereBetween('sales.created_at', [$start, $end])
                ->selectRaw('users.name as name, COUNT(*) as count, SUM(sales.total) as total, AVG(sales.total) as avg')
                ->groupBy('users.name')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($r) => [
                    'name' => $r->name,
                    'count' => (int) $r->count,
                    'total' => (float) $r->total,
                    'avg' => (float) $r->avg,
                ])
                ->toArray();

            return [
                'from' => $from,
                'to' => $to,
                'rows' => $rows,
            ];
        });
    }
}
