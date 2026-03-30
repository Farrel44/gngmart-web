<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Pesanan 7 Hari Terakhir';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '280px';

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(fn ($daysAgo) => Carbon::today()->subDays($daysAgo));

        // Shared cache key with StoreStatsWidget for raw grouped data
        $startDate = $days->first()->toDateString();
        $endDate = $days->last()->toDateString();

        $counts = Cache::remember('admin_order_chart_7days_raw', 300, function () use ($startDate, $endDate) {
            return Order::query()
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
                ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('total', 'date');
        });

        $orderCounts = $days->map(fn ($date) => $counts->get($date->toDateString(), 0))->toArray();

        $labels = $days->map(fn ($date) => $date->translatedFormat('d M'));

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pesanan',
                    'data' => $orderCounts,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                    'pointBackgroundColor' => '#ef4444',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
