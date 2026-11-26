<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use AuthorizesRequests;

    /**
     * List suppliers with search and a "products supplied" count.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Supplier::class);

        $suppliers = Supplier::query()
            ->withCount('products')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $this->authorize('create', Supplier::class);

        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return redirect()->route('suppliers.show', $supplier)->with('status', 'Supplier created.');
    }

    /**
     * Show a supplier with their products and recent stock-ins.
     */
    public function show(Supplier $supplier)
    {
        $this->authorize('view', $supplier);

        $products = $supplier->products()->latest()->get();

        $stockIns = StockMovement::query()
            ->where('type', 'in')
            ->whereHas('product', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->with('product')
            ->latest()
            ->take(20)
            ->get();

        return view('suppliers.show', compact('supplier', 'products', 'stockIns'));
    }

    public function edit(Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.show', $supplier)->with('status', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorize('delete', $supplier);

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('status', 'Supplier deleted.');
    }

    /**
     * Purchases by supplier: total stock-in value grouped by supplier.
     */
    public function report()
    {
        $this->authorize('viewAny', Supplier::class);

        $report = Supplier::query()
            ->leftJoin('products', 'products.supplier_id', '=', 'suppliers.id')
            ->leftJoin('stock_movements', function ($join) {
                $join->on('stock_movements.product_id', '=', 'products.id')
                    ->where('stock_movements.type', '=', 'in');
            })
            ->selectRaw('suppliers.id, suppliers.name, COALESCE(SUM(stock_movements.quantity * products.buying_price), 0) as total_purchases')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total_purchases')
            ->get();

        return view('suppliers.report', compact('report'));
    }
}
