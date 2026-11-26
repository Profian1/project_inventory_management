<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Note: stock_quantity is intentionally omitted — it is only set on
     * create and adjusted through the dedicated stock movement flow.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'buying_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($product->id)],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
