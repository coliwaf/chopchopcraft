<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case Mpesa    = 'mpesa';
    case Stripe   = 'stripe';
    case PayPal   = 'paypal';
    case Whatsapp = 'whatsapp';
    case Unpaid   = 'unpaid';

    public function label(): string
    {
        return match($this) {
            self::Mpesa    => 'M-Pesa',
            self::Stripe   => 'Stripe',
            self::PayPal   => 'PayPal',
            self::Whatsapp => 'WhatsApp Order',
            self::Unpaid   => 'Unpaid',
        };
    }
}
