<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions-widget';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function getActions(): array
    {
        return [
            [
                'label' => 'Tambah Produk',
                'icon' => 'heroicon-o-plus-circle',
                'url' => route('filament.admin.resources.products.create'),
                'color' => 'text-red-500',
                'bg' => 'bg-red-50',
            ],
            [
                'label' => 'Kelola Kategori',
                'icon' => 'heroicon-o-tag',
                'url' => route('filament.admin.resources.categories.index'),
                'color' => 'text-blue-500',
                'bg' => 'bg-blue-50',
            ],
            [
                'label' => 'Lihat Pesanan',
                'icon' => 'heroicon-o-shopping-bag',
                'url' => route('filament.admin.resources.orders.index'),
                'color' => 'text-green-500',
                'bg' => 'bg-green-50',
            ],
            [
                'label' => 'Kelola Promosi',
                'icon' => 'heroicon-o-megaphone',
                'url' => route('filament.admin.resources.promotions.index'),
                'color' => 'text-amber-500',
                'bg' => 'bg-amber-50',
            ],
        ];
    }
}
