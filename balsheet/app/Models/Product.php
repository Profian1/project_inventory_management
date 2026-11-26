<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sku',
        'name',
        'description',
        'category_id',
        'supplier_id',
        'buying_price',
        'selling_price',
        'stock_quantity',
        'reorder_level',
        'barcode',
        'image_path',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'buying_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'reorder_level' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * A product belongs to one (optional) category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * A product belongs to one (optional) supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * A product has many stock movements (its full stock history).
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * A product appears in many sale items.
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Accessor: gross profit per unit (selling price - buying price).
     */
    public function getMarginAttribute(): float
    {
        return round((float) $this->selling_price - (float) $this->buying_price, 2);
    }

    /**
     * Accessor: margin expressed as a percentage of the selling price.
     */
    public function getMarginPercentageAttribute(): float
    {
        $selling = (float) $this->selling_price;

        if ($selling == 0) {
            return 0.0;
        }

        return round((($selling - (float) $this->buying_price) / $selling) * 100, 2);
    }

    /**
     * Accessor: whether stock has fallen to or below the reorder level.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    /**
     * Scope: products that are at or below their reorder level.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'reorder_level');
    }
}
