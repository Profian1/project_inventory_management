<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    use AuthorizesRequests;

    /**
     * List products with search, filters, sorting, trash view and pagination.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->filteredQuery($request)
            ->when(
                $request->filled('sort') && in_array($request->sort, ['name', 'selling_price', 'stock_quantity', 'margin']),
                function ($query) use ($request) {
                    $direction = $request->direction === 'asc' ? 'asc' : 'desc';

                    // "margin" is computed, so sort with a raw expression.
                    if ($request->sort === 'margin') {
                        $query->orderByRaw("(selling_price - buying_price) {$direction}");
                    } else {
                        $query->orderBy($request->sort, $direction);
                    }
                },
                fn ($query) => $query->latest(),
            )
            ->paginate(15)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the product creation form.
     */
    public function create()
    {
        $this->authorize('create', Product::class);

        return view('products.create', [
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        // Auto-generate a sequential SKU (PRD-0001) when none is provided.
        if (empty($data['sku'])) {
            $data['sku'] = 'PRD-' . str_pad((int) Product::withTrashed()->max('id') + 1, 4, '0', STR_PAD_LEFT);
        }

        $data['image_path'] = $this->storeImage($request);

        $product = Product::create($data);

        return redirect()->route('products.show', $product)
            ->with('status', 'Product created successfully.');
    }

    /**
     * Show a single product with barcode, stock history and sales history.
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load(['category', 'supplier']);

        $stockMovements = $product->stockMovements()->with('user')->latest()->take(20)->get();
        $sales = $product->saleItems()->with('sale')->latest()->take(10)->get();

        return view('products.show', compact('product', 'stockMovements', 'sales'));
    }

    /**
     * Show the product edit form.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        return view('products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        // Replace the image only when a new one is uploaded.
        if ($request->hasFile('image')) {
            $this->deleteImage($product->image_path);
            $data['image_path'] = $this->storeImage($request);
        }

        $product->update($data);

        return redirect()->route('products.show', $product)
            ->with('status', 'Product updated successfully.');
    }

    /**
     * Soft-delete a product (admin only).
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()->route('products.index')
            ->with('status', 'Product moved to trash.');
    }

    /**
     * Restore a soft-deleted product (admin only).
     */
    public function restore(string $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $product);

        $product->restore();

        return back()->with('status', 'Product restored.');
    }

    /**
     * Permanently delete a product (admin only).
     */
    public function forceDelete(string $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $product);

        $this->deleteImage($product->image_path);
        $product->forceDelete();

        return back()->with('status', 'Product permanently deleted.');
    }

    /**
     * Apply a bulk action (activate / deactivate / delete) to selected products.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        // Deleting is admin-only; activate/deactivate is open to staff.
        if ($validated['action'] === 'delete') {
            $this->authorize('deleteAny', Product::class);
        }

        $products = Product::whereIn('id', $validated['ids'])->get();

        foreach ($products as $product) {
            if ($validated['action'] === 'activate') {
                $product->update(['is_active' => true]);
            } elseif ($validated['action'] === 'deactivate') {
                $product->update(['is_active' => false]);
            } elseif ($validated['action'] === 'delete') {
                $product->delete();
            }
        }

        return back()->with('status', 'Bulk action completed.');
    }

    /**
     * Export the filtered product list as a CSV download.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->filteredQuery($request)->get();

        $filename = 'products-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['SKU', 'Name', 'Category', 'Buying Price', 'Selling Price', 'Stock', 'Reorder Level', 'Status']);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->sku,
                    $product->name,
                    $product->category?->name,
                    $product->buying_price,
                    $product->selling_price,
                    $product->stock_quantity,
                    $product->reorder_level,
                    $product->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    /**
     * Search active products by name/SKU for the AJAX searchable select.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $products = Product::query()
            ->where('is_active', true)
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'sku', 'stock_quantity']);

        return response()->json($products);
    }

    /**
     * Build the filtered product query used by the index and CSV export.
     */
    private function filteredQuery(Request $request)
    {
        return Product::query()
            ->with(['category', 'supplier'])
            ->when($request->trash === '1', fn ($query) => $query->onlyTrashed())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->category_id))
            ->when($request->filled('supplier_id'), fn ($query) => $query->where('supplier_id', $request->supplier_id))
            ->when($request->filled('stock'), function ($query) use ($request) {
                if ($request->stock === 'in') {
                    $query->where('stock_quantity', '>', 0);
                } elseif ($request->stock === 'low') {
                    $query->whereColumn('stock_quantity', '<=', 'reorder_level');
                } elseif ($request->stock === 'out') {
                    $query->where('stock_quantity', 0);
                }
            });
    }

    /**
     * Store an uploaded product image and return its public path.
     */
    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('products', 'public');
    }

    /**
     * Delete a product image from storage.
     */
    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
