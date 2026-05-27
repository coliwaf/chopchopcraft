<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;
 
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price_override',
        'stock_qty',
        'low_stock_threshold',
        'size',
        'weight_kg',
        'dimensions',
        'is_active',
        'sort_order',
    ];
 
    protected $casts = [
        'price_override'      => 'decimal:2',
        'weight_kg'           => 'decimal:3',
        'dimensions'          => 'array',
        'is_active'           => 'boolean',
        'stock_qty'           => 'integer',
        'low_stock_threshold' => 'integer',
    ];
 
    // ─── Relationships ────────────────────────────────────────────────────────
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
 
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
 
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
 
    // ─── Scopes ──────────────────────────────────────────────────────────────
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
 
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_qty', '>', 0);
    }
 
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                     ->where('stock_qty', '>', 0);
    }
 
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_qty', 0);
    }
 
    // ─── Helpers ─────────────────────────────────────────────────────────────
    public function getEffectivePriceAttribute(): float
    {
        return $this->price_override ?? $this->product->base_price;
    }
 
    public function isInStock(): bool
    {
        return $this->stock_qty > 0;
    }
 
    public function isLowStock(): bool
    {
        return $this->stock_qty > 0 && $this->stock_qty <= $this->low_stock_threshold;
    }
 
    /**
     * Decrement stock and record a movement. Throws if insufficient stock.
     */
    public function deductStock(int $qty, ?int $orderId = null, string $note = ''): void
    {
        if ($this->stock_qty < $qty) {
            throw new \RuntimeException("Insufficient stock for variant {$this->sku}.");
        }
 
        $before = $this->stock_qty;
        $this->decrement('stock_qty', $qty);
 
        StockMovement::create([
            'product_variant_id' => $this->id,
            'order_id'           => $orderId,
            'type'               => 'sale',
            'quantity'           => -$qty,
            'stock_before'       => $before,
            'stock_after'        => $before - $qty,
            'note'               => $note,
        ]);
    }
 
    /**
     * Add stock and record a movement.
     */
    public function addStock(int $qty, string $type = 'restock', string $note = '', ?int $userId = null): void
    {
        $before = $this->stock_qty;
        $this->increment('stock_qty', $qty);
 
        StockMovement::create([
            'product_variant_id' => $this->id,
            'type'               => $type,
            'quantity'           => $qty,
            'stock_before'       => $before,
            'stock_after'        => $before + $qty,
            'note'               => $note,
            'created_by'         => $userId,
        ]);
    }
}
