<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use App\Services\SaleService;
use App\Services\StockService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private SaleService $saleService,
        private StockService $stockService,
    ) {
    }

    /**
     * Sales history with filters.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Sale::class);

        $sales = Sale::query()
            ->with(['customer', 'user', 'items'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Point-of-sale screen.
     */
    public function create()
    {
        $this->authorize('create', Sale::class);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'selling_price', 'stock_quantity', 'category_id']);

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $vatRate = (float) Setting::getValue('vat_rate', '16');

        return view('sales.create', compact('products', 'categories', 'vatRate'));
    }

    /**
     * Store a sale, its items, stock deduction and any credit debt.
     */
    public function store(StoreSaleRequest $request)
    {
        $items = json_decode($request->items, true);

        if (! is_array($items) || empty($items)) {
            return back()->withErrors(['items' => 'The cart is empty.']);
        }

        // Validate each item and check stock sufficiency before the transaction.
        $cart = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id'] ?? null);
            $quantity = (int) ($item['quantity'] ?? 0);

            if (! $product || $quantity <= 0) {
                return back()->withErrors(['items' => 'Invalid cart item.']);
            }

            if ($quantity > $product->stock_quantity) {
                return back()->withErrors(['items' => "Insufficient stock for {$product->name}."]);
            }

            $cart[] = ['product' => $product, 'quantity' => $quantity];
        }

        // Credit sales require a non-walk-in customer.
        if ($request->payment_method === 'credit' && ! $request->customer_id) {
            return back()->withErrors(['customer_id' => 'Credit sales require a customer.']);
        }

        // Recompute totals server-side (never trust the client).
        $subtotal = array_sum(array_map(fn ($i) => $i['product']->selling_price * $i['quantity'], $cart));
        $discount = min((float) $request->discount, $subtotal);
        $vatRate = (float) Setting::getValue('vat_rate', '16');
        $vatAmount = $request->apply_vat ? round(($subtotal - $discount) * $vatRate / 100, 2) : 0;
        $total = round($subtotal - $discount + $vatAmount, 2);

        $amountPaid = match ($request->payment_method) {
            'cash' => (float) $request->amount_paid,
            'credit' => 0,
            default => $total,
        };
        $change = $request->payment_method === 'cash' ? round($amountPaid - $total, 2) : 0;

        $sale = $this->saleService->createSale([
            'customer_id' => $request->customer_id ?: null,
            'items' => $cart,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'vat_amount' => $vatAmount,
            'total' => $total,
            'payment_method' => $request->payment_method,
            'amount_paid' => $amountPaid,
            'change_given' => max(0, $change),
        ], $request->user());

        return redirect()->route('sales.receipt', $sale)->with('status', 'Sale completed.');
    }

    /**
     * Show a single sale.
     */
    public function show(Sale $sale)
    {
        $this->authorize('view', $sale);

        $sale->load(['items', 'customer', 'user']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Printable receipt for a sale.
     */
    public function receipt(Sale $sale)
    {
        $this->authorize('view', $sale);

        $sale->load(['items', 'customer', 'user']);

        return view('sales.receipt', compact('sale'));
    }

    /**
     * Cancel a sale (admin only): restores stock and reverses any debt.
     */
    public function cancel(Sale $sale)
    {
        $this->authorize('cancel', $sale);

        if ($sale->status === 'cancelled') {
            return back()->with('status', 'Sale is already cancelled.');
        }

        DB::transaction(function () use ($sale) {
            // Return each item's quantity to stock.
            foreach ($sale->items as $item) {
                if ($item->product) {
                    $this->stockService->move(
                        $item->product,
                        'return',
                        $item->quantity,
                        "Cancel sale {$sale->invoice_number}",
                        auth()->id(),
                        $sale->invoice_number,
                    );
                }
            }

            // Reverse the credit debt and customer balance.
            if ($sale->debt) {
                $sale->debt->customer->decrement('credit_balance', $sale->debt->balance);
                $sale->debt->delete();
            }

            $sale->update(['status' => 'cancelled']);
        });

        return back()->with('status', 'Sale cancelled and stock restored.');
    }
}
