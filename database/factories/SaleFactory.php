<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 500, 50000);
        $discount = fake()->randomFloat(2, 0, 1000);
        $vat = round(($subtotal - $discount) * 0.16, 2);
        $total = round($subtotal - $discount + $vat, 2);
        $method = fake()->randomElement(['cash', 'mpesa', 'credit']);

        return [
            'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'vat_amount' => $vat,
            'total' => $total,
            'payment_method' => $method,
            // A credit sale is not paid upfront; cash/mpesa sales are paid in full.
            'amount_paid' => $method === 'credit' ? 0 : $total,
            'change_given' => 0,
            'status' => 'completed',
            'notes' => null,
        ];
    }
}
