<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class OrderStatusChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Status Pesanan';

    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '280px';

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $statuses = [
            Order::STATUS_PENDING => ['label' => 'Menunggu', 'color' => '#f59e0b'],
            Order::STATUS_PAID => ['label' => 'Dibayar', 'color' => '#3b82f6'],
            Order::STATUS_PROCESSING => ['label' => 'Diproses', 'color' => '#6366f1'],
            Order::STATUS_SHIPPED => ['label' => 'Dikirim', 'color' => '#8b5cf6'],
            Order::STATUS_COMPLETED => ['label' => 'Selesai', 'color' => '#10b981'],
            Order::STATUS_CANCELLED => ['label' => 'Dibatalkan', 'color' => '#ef4444'],
        ];

        // Single GROUP BY query instead of 6 separate queries, cached for 5 minutes
        $statusCounts = Cache::remember('admin_order_status_chart', 300, function () {
            return Order::query()
                ->selectRaw('order_status, COUNT(*) as total')
                ->groupBy('order_status')
                ->pluck('total', 'order_status');
        });

        $counts = [];
        $labels = [];
        $colors = [];

        foreach ($statuses as $status => $meta) {
            $counts[] = $statusCounts->get($status, 0);
            $labels[] = $meta['label'];
            $colors[] = $meta['color'];
        }

        return [
            'datasets' => [
                [
                    'data' => $counts,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                    'hoverOffset' => 8,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 16,
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                    ],
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
