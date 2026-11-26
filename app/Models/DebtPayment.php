<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebtPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'debt_id',
        'amount',
        'payment_method',
        'user_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * A debt payment belongs to one debt.
     */
    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    /**
     * A debt payment is recorded by one user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
