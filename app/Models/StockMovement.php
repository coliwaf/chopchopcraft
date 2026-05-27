<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public $timestamps = true;
    public const UPDATED_AT = null; // immutable records — no updated_at needed

    protected $fillable = [
        'product_variant_id',
        'order_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'note',
        'created_by',
    ];

    protected $casts = [
        'type'     => StockMovementType::class,
        'quantity' => 'integer',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
