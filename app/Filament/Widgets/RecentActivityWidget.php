<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity-widget';

    protected static ?int $sort = 6;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 1;

    public function getActivities(): Collection
    {
        return Cache::remember('admin_recent_activities', 120, function () {
            $activities = collect();

            // Recent user registrations
            User::latest()->take(3)->get()->each(function ($user) use ($activities) {
                $activities->push([
                    'icon' => 'heroicon-o-user-plus',
                    'color' => 'text-blue-500',
                    'bg' => 'bg-blue-50',
                    'message' => $user->name.' mendaftar',
                    'time' => $user->created_at,
                ]);
            });

            // Recent orders
            Order::with('user')->latest()->take(3)->get()->each(function ($order) use ($activities) {
                $customerName = $order->user?->name ?? 'Pelanggan';
                $activities->push([
                    'icon' => 'heroicon-o-shopping-cart',
                    'color' => 'text-green-500',
                    'bg' => 'bg-green-50',
                    'message' => 'Pesanan baru dari '.$customerName,
                    'time' => $order->created_at,
                ]);
            });

            // Recent products added
            Product::latest()->take(3)->get()->each(function ($product) use ($activities) {
                $activities->push([
                    'icon' => 'heroicon-o-cube',
                    'color' => 'text-amber-500',
                    'bg' => 'bg-amber-50',
                    'message' => 'Produk ditambahkan: '.$product->name,
                    'time' => $product->created_at,
                ]);
            });

            return $activities->sortByDesc('time')->take(6)->values();
        });
    }
}
