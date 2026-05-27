<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Sale       = 'sale';
    case Restock    = 'restock';
    case Adjustment = 'adjustment';
    case Return     = 'return';
    case WriteOff   = 'write_off';

    public function label(): string
    {
        return match($this) {
            self::Sale       => 'Sale',
            self::Restock    => 'Restock',
            self::Adjustment => 'Manual Adjustment',
            self::Return     => 'Customer Return',
            self::WriteOff   => 'Write-off',
        };
    }

    public function isIncoming(): bool
    {
        return in_array($this, [self::Restock, self::Return]);
    }
}
