<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'credit_balance',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credit_balance' => 'decimal:2',
    ];

    /**
     * A customer has many sales.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * A customer has many debts (credit sales they owe).
     */
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }
}
