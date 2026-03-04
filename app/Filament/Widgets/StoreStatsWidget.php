<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $totalUsers = User::where('email_verified_at', '!=', null)->count();
        $totalOrders = Order::count();
        $monthlyOrders = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('Total Produk', $totalProducts)
                ->description('Produk tersedia')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('danger')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Total Pesanan', $totalOrders)
                ->description('Semua pesanan')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('danger')
                ->icon('heroicon-o-receipt-percent'),

            Stat::make('Pesanan Bulan Ini', $monthlyOrders)
                ->description('Bulan ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success')
                ->icon('heroicon-o-calendar'),

            Stat::make('Total Pelanggan', $totalUsers)
                ->description('Pengguna aktif')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->icon('heroicon-o-user-group'),
        ];
    }
}
