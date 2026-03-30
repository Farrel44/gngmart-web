<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StoreStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = Cache::remember('admin_store_stats', 300, function () {
            $startDate = Carbon::today()->subDays(6)->toDateString();
            $endDate = Carbon::today()->toDateString();

            // Reuse the same cache key as OrderChartWidget to avoid duplicate queries
            $ordersByDay = Cache::remember('admin_order_chart_7days_raw', 300, function () use ($startDate, $endDate) {
                return Order::query()
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
                    ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->pluck('total', 'date');
            });

            // Single GROUP BY query for 7 days of users (replaces 7 queries)
            $usersByDay = User::query()
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
                ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('total', 'date');

            $days = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i)->toDateString());

            return [
                'products' => Product::count(),
                'users' => User::whereNotNull('email_verified_at')->count(),
                'orders' => Order::count(),
                'monthly' => Order::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'orders_7days' => $days->map(fn ($date) => $ordersByDay->get($date, 0))->toArray(),
                'users_7days' => $days->map(fn ($date) => $usersByDay->get($date, 0))->toArray(),
            ];
        });

        return [
            Stat::make('Total Produk', $stats['products'])
                ->description('Produk tersedia')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('danger')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Total Pesanan', $stats['orders'])
                ->description('Semua pesanan')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('danger')
                ->icon('heroicon-o-receipt-percent')
                ->chart($stats['orders_7days']),

            Stat::make('Pesanan Bulan Ini', $stats['monthly'])
                ->description('Bulan '.now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success')
                ->icon('heroicon-o-calendar'),

            Stat::make('Total Pelanggan', $stats['users'])
                ->description('Pengguna terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->icon('heroicon-o-user-group')
                ->chart($stats['users_7days']),
        ];
    }
}
