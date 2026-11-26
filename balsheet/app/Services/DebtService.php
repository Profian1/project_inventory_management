<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\DebtPayment;
use Illuminate\Support\Facades\DB;

class DebtService
{
    /**
     * Record a payment against a debt and update the debt + customer balance.
     *
     * @throws \InvalidArgumentException if the debt is already paid or the amount is invalid.
     */
    public function recordPayment(Debt $debt, float $amount, string $method, ?string $notes = null, ?int $userId = null): DebtPayment
    {
        return DB::transaction(function () use ($debt, $amount, $method, $notes, $userId) {
            // Lock the debt row to avoid concurrent payments.
            $debt = Debt::whereKey($debt->id)->lockForUpdate()->firstOrFail();

            if ($debt->status === 'paid' || $debt->balance <= 0) {
                throw new \InvalidArgumentException('This debt is already fully paid.');
            }

            if ($amount <= 0 || $amount > $debt->balance) {
                throw new \InvalidArgumentException('Payment amount must be between 0.01 and the outstanding balance.');
            }

            $payment = $debt->payments()->create([
                'amount' => $amount,
                'payment_method' => $method,
                'notes' => $notes,
                'user_id' => $userId,
            ]);

            $paid = $debt->amount_paid + $amount;
            $balance = $debt->balance - $amount;

            // Fully paid => 'paid'; partially paid => 'partial'.
            $status = $balance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

            $debt->update([
                'amount_paid' => $paid,
                'balance' => $balance,
                'status' => $status,
            ]);

            // Reduce the customer's total credit balance by the amount paid.
            $debt->customer()->decrement('credit_balance', $amount);

            return $payment;
        });
    }
}
