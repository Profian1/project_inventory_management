<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Debt;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(private StockService $stockService)
    {
    }

    /**
     * Create a sale, its items and any credit debt inside a single transaction.
     *
     * @param  array  $data  Validated sale data with resolved items
     *                       (each item is ['product' => Product, 'quantity' => int]).
     */
    public function createSale(array $data, User $user): Sale
    {
        return DB::transaction(function () use ($data, $user) {
            $sale = Sale::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $data['customer_id'],
                'user_id' => $user->id,
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'],
                'vat_amount' => $data['vat_amount'],
                'total' => $data['total'],
                'payment_method' => $data['payment_method'],
                'amount_paid' => $data['amount_paid'],
                'change_given' => $data['change_given'],
                'status' => 'completed',
            ]);

            foreach ($data['items'] as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];

                // Snapshot the product name and buying price for history.
                $sale->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $product->selling_price,
                    'buying_price' => $product->buying_price,
                    'subtotal' => $product->selling_price * $quantity,
                ]);

                // Deduct stock and log the movement.
                $this->stockService->move(
                    $product,
                    'sale',
                    $quantity,
                    "Sale {$sale->invoice_number}",
                    $user->id,
                    $sale->invoice_number,
                );
            }

            // A credit sale creates a debt and increases the customer's balance.
            if ($data['payment_method'] === 'credit') {
                $customer = Customer::findOrFail($data['customer_id']);

                Debt::create([
                    'customer_id' => $customer->id,
                    'sale_id' => $sale->id,
                    'original_amount' => $sale->total,
                    'amount_paid' => 0,
                    'balance' => $sale->total,
                    'status' => 'unpaid',
                ]);

                $customer->increment('credit_balance', $sale->total);
            }

            return $sale;
        });
    }

    /**
     * Generate a sequential, date-scoped invoice number (INV-YYYYMMDD-0001).
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';

        $last = Sale::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
