<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    // v4: must include array in union type
    protected int|string|array  $columnSpan = 'full';

    public function getHeading(): string|Htmlable|null
    {
        return 'Revenue — last 30 days';
    }

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(function (int $daysAgo) {
            $date    = now()->subDays($daysAgo)->toDateString();
            $revenue = Order::whereDate('created_at', $date)->paid()->sum('total');

            return ['date' => $date, 'revenue' => (float) $revenue];
        });

        return [
            'datasets' => [[
                'label'           => 'Revenue (KES)',
                'data'            => $days->pluck('revenue')->toArray(),
                'borderColor'     => '#96511e',
                'backgroundColor' => 'rgba(150, 81, 30, 0.08)',
                'fill'            => true,
                'tension'         => 0.4,
            ]],
            'labels' => $days->map(fn($d) => Carbon::parse($d['date'])->format('d M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

// use App\Models\Order;
// use Filament\Widgets\ChartWidget;
// use Illuminate\Support\Carbon;

// class RevenueChartWidget extends ChartWidget
// {
// 	protected static ?string $heading = 'Revenue — last 30 days';
// 	protected static ?int    $sort    = 2;
// 	/* public function getColumnSpan(): int|string|null
// 	{
// 		return 'full';
// 	}
//  */
// 	protected function getData(): array
// 	{
// 		$days = collect(range(29, 0))->map(function (int $daysAgo) {
// 			$date = now()->subDays($daysAgo)->toDateString();

// 			$revenue = Order::whereDate('created_at', $date)
// 				->paid()
// 				->sum('total');

// 			return ['date' => $date, 'revenue' => (float) $revenue];
// 		});

// 		return [
// 			'datasets' => [[
// 				'label'           => 'Revenue (KES)',
// 				'data'            => $days->pluck('revenue')->toArray(),
// 				'borderColor'     => '#96511e',
// 				'backgroundColor' => 'rgba(150, 81, 30, 0.08)',
// 				'fill'            => true,
// 				'tension'         => 0.4,
// 			]],
// 			'labels' => $days->map(fn($d) => Carbon::parse($d['date'])->format('d M'))->toArray(),
// 		];
// 	}

// 	protected function getType(): string
// 	{
// 		return 'line';
// 	}
// }