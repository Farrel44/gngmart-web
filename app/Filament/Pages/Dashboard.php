<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OrderChartWidget;
use App\Filament\Widgets\OrderStatusChartWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\StoreStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getTitle(): string
    {
        return 'Dashboard';
    }

    public function getHeading(): string
    {
        return 'Selamat Datang di GnG Mart Admin';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola toko online Anda dengan mudah dan efisien';
    }

    public function getWidgets(): array
    {
        return [
            StoreStatsWidget::class,       // full - stat cards with mini charts
            QuickActionsWidget::class,      // full - 4 action buttons in a row
            OrderChartWidget::class,        // full - line chart (orders 7 days)
            OrderStatusChartWidget::class,  // 1 col - donut chart
            RecentActivityWidget::class,    // 1 col - activity feed
            RecentOrdersWidget::class,      // full - orders table
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}
