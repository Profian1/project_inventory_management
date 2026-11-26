<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Record a stock movement and update the product's quantity atomically.
     *
     * @param  int  $qty  Positive for "in", negative for "out", and either
     *                    sign for "adjustment" (add/subtract). For an
     *                    adjustment "set to value", pass the computed delta.
     *
     * @throws \InvalidArgumentException when the movement would make stock negative.
     */
    public function move(
        Product $product,
        string $type,
        int $qty,
        ?string $reason = null,
        ?int $userId = null,
        ?string $reference = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $type, $qty, $reason, $userId, $reference) {
            // Lock the row to prevent race conditions between concurrent movements.
            $product = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

            // Resolve the signed delta (how much the stock actually changes).
            $delta = match ($type) {
                'in', 'return' => abs($qty),
                'out', 'sale' => -abs($qty),
                'adjustment' => $qty,
                default => throw new \InvalidArgumentException("Unknown stock movement type [{$type}]."),
            };

            $newStock = $product->stock_quantity + $delta;

            // Guard rail: stock can never go negative.
            if ($newStock < 0) {
                throw new \InvalidArgumentException("Insufficient stock: only {$product->stock_quantity} available.");
            }

            $product->update(['stock_quantity' => $newStock]);

            return StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $delta,
                'reason' => $reason,
                'reference' => $reference,
                'user_id' => $userId,
            ]);
        });
    }
}
