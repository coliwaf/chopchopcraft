<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
       protected $fillable = [
        'order_id', 'gateway', 'gateway_ref', 'gateway_checkout_id',
        'amount', 'currency', 'status', 'raw_response', 'paid_at',
    ];
 
    protected $casts = [
        'amount'       => 'decimal:2',
        'gateway'      => PaymentGateway::class,
        'status'       => PaymentStatus::class,
        'raw_response' => 'array',
        'paid_at'      => 'datetime',
    ];
 
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
 
    public function markPaid(string $ref): void
    {
        $this->update(['status' => PaymentStatus::Paid, 'gateway_ref' => $ref, 'paid_at' => now()]);
        $this->order->markAsPaid($ref);
    }
}
