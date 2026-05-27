<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCode extends Model
{
    use SoftDeletes;
 
    protected $fillable = [
        'code', 'description', 'type', 'value',
        'minimum_order_amount', 'uses_limit', 'uses_count',
        'per_customer_limit', 'is_active', 'starts_at', 'expires_at',
    ];
 
    protected $casts = [
        'type'                  => DiscountType::class,
        'value'                 => 'decimal:2',
        'minimum_order_amount'  => 'decimal:2',
        'is_active'             => 'boolean',
        'starts_at'             => 'datetime',
        'expires_at'            => 'datetime',
    ];
 
    public function usages(): HasMany
    {
        return $this->hasMany(DiscountCodeUsage::class);
    }
 
    // ─── Scopes ──────────────────────────────────────────────────────────────
    public function scopeValid(Builder $q): Builder
    {
        return $q->where('is_active', true)
                 ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                 ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                 ->where(fn($q) => $q->whereNull('uses_limit')->orWhereColumn('uses_count', '<', 'uses_limit'));
    }
 
    // ─── Validation ──────────────────────────────────────────────────────────
    /**
     * Returns null if valid, or an error string if not applicable.
     */
    public function validateForCustomer(Customer $customer, float $subtotal): ?string
    {
        if (! $this->is_active)             return 'This discount code is inactive.';
        if ($this->starts_at && $this->starts_at->isFuture()) return 'This code is not yet active.';
        if ($this->expires_at && $this->expires_at->isPast()) return 'This discount code has expired.';
        if ($this->uses_limit && $this->uses_count >= $this->uses_limit) return 'This code has reached its usage limit.';
        if ($subtotal < $this->minimum_order_amount) return "Minimum order of KES {$this->minimum_order_amount} required.";
 
        $customerUses = $this->usages()->where('customer_id', $customer->id)->count();
        if ($customerUses >= $this->per_customer_limit) return 'You have already used this discount code.';
 
        return null;
    }
 
    /**
     * Calculate discount amount from a given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        return match($this->type) {
            DiscountType::Percent => round($subtotal * ($this->value / 100), 2),
            DiscountType::Fixed   => min($this->value, $subtotal),
        };
    }
 
    public function incrementUsage(): void
    {
        $this->increment('uses_count');
    }
}
