<?php

namespace App\Http\Requests;

use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Sale::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'string'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'discount' => ['required', 'numeric', 'min:0'],
            'apply_vat' => ['required', 'in:0,1'],
            'payment_method' => ['required', 'in:cash,mpesa,credit'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
