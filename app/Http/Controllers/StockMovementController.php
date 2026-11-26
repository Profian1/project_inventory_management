<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /**
     * List stock movements with filters and summary cards.
     */
    public function index(Request $request)
    {
        $movements = StockMovement::query()
            ->with(['product', 'user'])
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->product_id))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $month = [now()->startOfMonth(), now()];

        $stockIn = (int) StockMovement::where('type', 'in')->whereBetween('created_at', $month)->sum('quantity');
        $stockOut = (int) abs(StockMovement::where('type', 'out')->whereBetween('created_at', $month)->sum('quantity'));

        return view('stock.index', [
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'stockIn' => $stockIn,
            'stockOut' => $stockOut,
            'netChange' => $stockIn - $stockOut,
        ]);
    }

    /**
     * Show the stock movement form.
     */
    public function create(Request $request)
    {
        return view('stock.create', [
            'type' => $request->get('type', 'in'),
        ]);
    }

    /**
     * Store a stock movement and update the product's quantity.
     */
    public function store(StoreStockMovementRequest $request, StockService $stockService)
    {
        $validated = $request->validated();
        $product = Product::findOrFail($validated['product_id']);

        // Resolve the signed quantity the service expects.
        $qty = $this->resolveQuantity($validated, $product);

        try {
            $stockService->move(
                $product,
                $validated['type'],
                $qty,
                $validated['reason'] ?? null,
                $request->user()->id,
                $validated['reference'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return redirect()->route('stock.index')->with('status', 'Stock movement recorded.');
    }

    /**
     * Show all movements for a single product.
     */
    public function productMovements(Product $product)
    {
        $movements = $product->stockMovements()->with('user')->latest()->paginate(20);

        return view('stock.product', compact('product', 'movements'));
    }

    /**
     * Convert the submitted quantity into the signed delta the service expects.
     */
    private function resolveQuantity(array $validated, Product $product): int
    {
        if ($validated['type'] !== 'adjustment') {
            return (int) $validated['quantity'];
        }

        // "set" mode: the quantity is the target level, so convert to a delta.
        if (($validated['adjustment_mode'] ?? 'add_subtract') === 'set') {
            return (int) $validated['quantity'] - (int) $product->stock_quantity;
        }

        // "add/subtract" mode: the quantity is already a signed delta.
        return (int) $validated['quantity'];
    }
}
