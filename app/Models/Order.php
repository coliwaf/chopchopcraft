<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;
 
    protected $fillable = [
        'order_number', 'customer_id', 'discount_code_id',
        'subtotal', 'discount_amount', 'shipping_amount', 'total',
        'status', 'payment_method', 'payment_status',
        'shipping_name', 'shipping_phone',
        'shipping_address_line1', 'shipping_address_line2',
        'shipping_city', 'shipping_county', 'shipping_postal_code', 'shipping_country',
        'whatsapp_confirmation_sent_at', 'whatsapp_order_sent_at',
        'internal_notes', 'tracking_number',
    ];
 
    protected $casts = [
        'subtotal'                       => 'decimal:2',
        'discount_amount'                => 'decimal:2',
        'shipping_amount'                => 'decimal:2',
        'total'                          => 'decimal:2',
        'status'                         => OrderStatus::class,
        'payment_method'                 => PaymentGateway::class,
        'payment_status'                 => PaymentStatus::class,
        'whatsapp_confirmation_sent_at'  => 'datetime',
        'whatsapp_order_sent_at'         => 'datetime',
    ];
 
    // ─── Relationships ────────────────────────────────────────────────────────
    public function customer(): BelongsTo   { return $this->belongsTo(Customer::class); }
    public function discountCode(): BelongsTo { return $this->belongsTo(DiscountCode::class); }
    public function items(): HasMany        { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany     { return $this->hasMany(Payment::class); }
    public function latestPayment(): HasOne { return $this->hasOne(Payment::class)->latestOfMany(); }
 
    // ─── Scopes ──────────────────────────────────────────────────────────────
    public function scopeToday(Builder $q): Builder
    {
        return $q->whereDate('created_at', today());
    }
 
    public function scopeLast30Days(Builder $q): Builder
    {
        return $q->where('created_at', '>=', now()->subDays(30));
    }
 
    public function scopePaid(Builder $q): Builder
    {
        return $q->where('payment_status', PaymentStatus::Paid);
    }
 
    public function scopeByStatus(Builder $q, OrderStatus $status): Builder
    {
        return $q->where('status', $status);
    }
 
    // ─── Helpers ─────────────────────────────────────────────────────────────
    public static function generateOrderNumber(): string
    {
        $year  = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return sprintf('CB-%s-%05d', $year, $count);
    }
 
    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }
 
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [OrderStatus::Pending, OrderStatus::Confirmed]);
    }
 
    public function markAsPaid(?string $gatewayRef = null): void
    {
        $this->update(['payment_status' => PaymentStatus::Paid, 'status' => OrderStatus::Confirmed]);
 
        // Update customer's last_ordered_at
        $this->customer->update(['last_ordered_at' => now()]);
    }
}
