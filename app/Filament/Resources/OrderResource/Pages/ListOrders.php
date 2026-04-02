<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /**
     * Admin tidak bisa membuat order baru dari panel.
     * Order hanya dibuat oleh customer melalui checkout.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Daftar Pesanan';
    }
}
