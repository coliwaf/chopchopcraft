<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
 
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'email', 'phone',
        'whatsapp_number', 'notes', 'address_line1', 'address_line2',
        'city', 'county', 'postal_code', 'country', 'source',
        'marketing_opt_in', 'last_ordered_at',
    ];
 
    protected $casts = [
        'marketing_opt_in' => 'boolean',
        'last_ordered_at'  => 'datetime',
    ];
 
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
    public function orders(): HasMany      { return $this->hasMany(Order::class); }
 
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
 
    public function getWhatsappContactAttribute(): string
    {
        return $this->whatsapp_number ?? $this->phone ?? '';
    }
 
    public function scopeWithPurchases(Builder $q): Builder
    {
        return $q->has('orders');
    }
 
    public function scopeLastWeek(Builder $q): Builder
    {
        return $q->where('created_at', '>=', now()->subWeek());
    }
 
    public function getTotalSpentAttribute(): float
    {
        return $this->orders()
                    ->where('payment_status', 'paid')
                    ->sum('total');
    }
}
