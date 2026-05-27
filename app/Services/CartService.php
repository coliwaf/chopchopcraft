<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Session-based shopping cart.
 *
 * Cart structure in session:
 * [
 *   'variant_id' => [
 *       'variant_id'   => int,
 *       'product_name' => string,
 *       'variant_name' => string,
 *       'sku'          => string,
 *       'price'        => float,
 *       'qty'          => int,
 *       'image'        => string|null,
 *   ],
 *   ...
 * ]
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    public function add(int $variantId, int $qty = 1): void
    {
        $variant = ProductVariant::with('product')->findOrFail($variantId);

        if (! $variant->is_active || ! $variant->product->is_active) {
            throw new \RuntimeException('This product is no longer available.');
        }

        if ($variant->stock_qty < $qty) {
            throw new \RuntimeException("Only {$variant->stock_qty} units left in stock.");
        }

        $cart = $this->all();
        $key  = (string) $variantId;

        if (isset($cart[$key])) {
            $newQty = $cart[$key]['qty'] + $qty;
            if ($variant->stock_qty < $newQty) {
                throw new \RuntimeException("Only {$variant->stock_qty} units available.");
            }
            $cart[$key]['qty'] = $newQty;
        } else {
            $cart[$key] = [
                'variant_id'   => $variant->id,
                'product_name' => $variant->product->name,
                'variant_name' => $variant->name,
                'sku'          => $variant->sku,
                'price'        => (float) $variant->effective_price,
                'qty'          => $qty,
                'image'        => $variant->product->getFirstMediaUrl('images', 'thumb') ?: null,
            ];
        }

        Session::put(self::SESSION_KEY, $cart);
    }

    public function update(int $variantId, int $qty): void
    {
        $cart = $this->all();
        $key  = (string) $variantId;

        if ($qty <= 0) {
            $this->remove($variantId);
            return;
        }

        $variant = ProductVariant::findOrFail($variantId);
        if ($variant->stock_qty < $qty) {
            throw new \RuntimeException("Only {$variant->stock_qty} units available.");
        }

        if (isset($cart[$key])) {
            $cart[$key]['qty'] = $qty;
            Session::put(self::SESSION_KEY, $cart);
        }
    }

    public function remove(int $variantId): void
    {
        $cart = $this->all();
        unset($cart[(string) $variantId]);
        Session::put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /** @return array<string, array> */
    public function all(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function items(): Collection
    {
        return collect($this->all());
    }

    public function isEmpty(): bool
    {
        return empty($this->all());
    }

    public function count(): int
    {
        return $this->items()->sum('qty');
    }

    public function subtotal(): float
    {
        return $this->items()->sum(fn($item) => $item['price'] * $item['qty']);
    }

    /** Apply a validated discount code and return the discount amount */
    public function applyDiscount(\App\Models\DiscountCode $code): float
    {
        return $code->calculateDiscount($this->subtotal());
    }

    /** Re-validate stock for all items (call before order creation) */
    public function validateStock(): array
    {
        $errors = [];
        foreach ($this->all() as $item) {
            $variant = ProductVariant::find($item['variant_id']);
            if (! $variant || ! $variant->is_active) {
                $errors[] = "{$item['product_name']} ({$item['variant_name']}) is no longer available.";
            } elseif ($variant->stock_qty < $item['qty']) {
                $errors[] = "Only {$variant->stock_qty} units of {$item['product_name']} ({$item['variant_name']}) remaining.";
            }
        }
        return $errors;
    }

    /** Serialise cart for Inertia shared props */
    public function toArray(): array
    {
        return [
            'items'    => array_values($this->all()),
            'count'    => $this->count(),
            'subtotal' => $this->subtotal(),
        ];
    }
}
