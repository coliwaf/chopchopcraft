<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    // v4: property instead of getPollingInterval() method
    protected static ?string $pollingInterval = '60s';
    protected static ?int    $sort            = 1;

    protected function getStats(): array
    {
        $revenue30d   = Order::last30Days()->paid()->sum('total');
        $revenue7d    = Order::where('created_at', '>=', now()->subDays(7))->paid()->sum('total');
        $ordersToday  = Order::today()->count();
        $pending      = Order::where('status', OrderStatus::Pending)->count();
        $lowStock     = ProductVariant::lowStock()->count();
        $outOfStock   = ProductVariant::outOfStock()->count();
        $newCustomers = Customer::lastWeek()->count();

        return [
            Stat::make('Revenue (30 days)', 'KES ' . number_format($revenue30d, 0))
                ->description('KES ' . number_format($revenue7d, 0) . ' last 7 days')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Orders today', (string) $ordersToday)
                ->description($pending . ' pending confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pending > 0 ? 'warning' : 'success'),

            Stat::make('Stock alerts', $lowStock . ' low · ' . $outOfStock . ' out')
                ->description('Variants needing attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($outOfStock > 0 ? 'danger' : ($lowStock > 0 ? 'warning' : 'success')),

            Stat::make('New customers (7d)', (string) $newCustomers)
                ->description('Registered this week')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
        ];
    }
}
