<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending  = 'pending';
    case Paid     = 'paid';
    case Failed   = 'failed';
    case Refunded = 'refunded';
    case Partial  = 'partial';

    public function color(): string
    {
        return match ($this) {
            self::Pending  => 'warning',
            self::Paid     => 'success',
            self::Failed   => 'danger',
            self::Refunded => 'gray',
            self::Partial  => 'info',
        };
    }
}
