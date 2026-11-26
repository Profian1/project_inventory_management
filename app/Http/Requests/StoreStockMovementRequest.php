<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStockMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:in,out,adjustment'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer'],
            'adjustment_mode' => ['required_if:type,adjustment', 'in:add_subtract,set'],
            'reason' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Post-validation checks that depend on the product's current stock.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $product = Product::find($this->input('product_id'));

                if (! $product) {
                    return;
                }

                $type = $this->input('type');
                $qty = (int) $this->input('quantity');

                // Stock in / out require a positive quantity.
                if ($type !== 'adjustment' && $qty <= 0) {
                    $validator->errors()->add('quantity', 'Quantity must be greater than zero for stock in/out.');

                    return;
                }

                // Stock out cannot exceed the available stock.
                if ($type === 'out' && $qty > $product->stock_quantity) {
                    $validator->errors()->add('quantity', "Insufficient stock. Available: {$product->stock_quantity}.");

                    return;
                }

                // Adjustment "set" mode: the quantity is the target stock level.
                if ($type === 'adjustment' && $this->input('adjustment_mode', 'add_subtract') === 'set' && $qty < 0) {
                    $validator->errors()->add('quantity', 'New stock quantity cannot be negative.');

                    return;
                }

                // Adjustment "add/subtract" mode: the quantity is a signed delta.
                if ($type === 'adjustment' && $this->input('adjustment_mode', 'add_subtract') === 'add_subtract' && $product->stock_quantity + $qty < 0) {
                    $validator->errors()->add('quantity', "Adjustment would make stock negative. Available: {$product->stock_quantity}.");
                }
            },
        ];
    }
}
