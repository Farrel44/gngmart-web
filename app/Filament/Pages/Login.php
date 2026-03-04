<?php

namespace App\Filament\Pages;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-on-rectangle';

    public function getTitle(): string
    {
        return 'Login Admin GnG Mart';
    }

    public function getHeading(): string
    {
        return 'GnG Mart Admin Panel';
    }
}
