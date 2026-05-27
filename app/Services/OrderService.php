<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DiscountCode;
use App\Models\DiscountCodeUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Enums\PaymentGateway;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private CartService $cart) {}

    /**
     * Create an order from the current cart.
     *
     * @param Customer        $customer
     * @param array           $shipping    Validated shipping fields
     * @param PaymentGateway  $gateway
     * @param DiscountCode|null $discount
     * @return Order
     */
    public function createFromCart(
        Customer      $customer,
        array         $shipping,
        PaymentGateway $gateway,
        ?DiscountCode  $discount = null,
    ): Order {
        // Final stock validation before we touch anything
        $errors = $this->cart->validateStock();
        if ($errors) {
            throw new \RuntimeException(implode(' ', $errors));
        }

        return DB::transaction(function () use ($customer, $shipping, $gateway, $discount) {
            $subtotal        = $this->cart->subtotal();
            $discountAmount  = $discount ? $discount->calculateDiscount($subtotal) : 0.0;
            $shippingAmount  = $this->calculateShipping($shipping['city'] ?? '');
            $total           = max(0, $subtotal - $discountAmount + $shippingAmount);

            // 1. Create order
            $order = Order::create([
                'order_number'           => Order::generateOrderNumber(),
                'customer_id'            => $customer->id,
                'discount_code_id'       => $discount?->id,
                'subtotal'               => $subtotal,
                'discount_amount'        => $discountAmount,
                'shipping_amount'        => $shippingAmount,
                'total'                  => $total,
                'payment_method'         => $gateway,
                'payment_status'         => 'pending',
                'status'                 => 'pending',
                'shipping_name'          => $shipping['name'],
                'shipping_phone'         => $shipping['phone'],
                'shipping_address_line1' => $shipping['address_line1'],
                'shipping_address_line2' => $shipping['address_line2'] ?? null,
                'shipping_city'          => $shipping['city'],
                'shipping_county'        => $shipping['county'] ?? null,
                'shipping_postal_code'   => $shipping['postal_code'] ?? null,
                'shipping_country'       => $shipping['country'] ?? 'KE',
            ]);

            // 2. Create order items + deduct stock atomically
            foreach ($this->cart->all() as $item) {
                OrderItem::create([
                    'order_id'            => $order->id,
                    'product_variant_id'  => $item['variant_id'],
                    'product_name'        => $item['product_name'],
                    'variant_name'        => $item['variant_name'],
                    'sku'                 => $item['sku'],
                    'qty'                 => $item['qty'],
                    'unit_price'          => $item['price'],
                    'line_total'          => $item['price'] * $item['qty'],
                ]);

                // Deduct stock and log movement inside ProductVariant
                $variant = \App\Models\ProductVariant::lockForUpdate()->findOrFail($item['variant_id']);
                $variant->deductStock($item['qty'], $order->id, "Order {$order->order_number}");
            }

            // 3. Create pending payment record
            Payment::create([
                'order_id' => $order->id,
                'gateway'  => $gateway,
                'amount'   => $total,
                'currency' => 'KES',
                'status'   => 'pending',
            ]);

            // 4. Track discount usage
            if ($discount) {
                DiscountCodeUsage::create([
                    'discount_code_id' => $discount->id,
                    'customer_id'      => $customer->id,
                    'order_id'         => $order->id,
                ]);
                $discount->incrementUsage();
            }

            // 5. Update customer's last_ordered_at
            $customer->touch('last_ordered_at');

            return $order;
        });
    }

    private function calculateShipping(string $city): float
    {
        // Simple flat-rate logic — adjust to your actual rates
        $nairobi = ['nairobi', 'westlands', 'karen', 'kileleshwa', 'lavington', 'parklands'];
        return in_array(strtolower($city), $nairobi) ? 200.0 : 400.0;
    }
}
