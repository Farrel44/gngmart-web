<?php

namespace App\Filament\Pages;

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
}
