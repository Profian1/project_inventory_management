<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'sale_id',
        'original_amount',
        'amount_paid',
        'balance',
        'due_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'original_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * A debt belongs to one customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * A debt belongs to one (optional) sale.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * A debt has many payments made towards it.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    /**
     * Scope: debts that still have an outstanding balance.
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('status', '!=', 'paid')->where('balance', '>', 0);
    }
}
