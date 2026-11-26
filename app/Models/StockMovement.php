<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reason',
        'user_id',
        'reference',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * A stock movement belongs to one product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * A stock movement is recorded by one user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
