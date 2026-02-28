<?php

namespace App\Filament\Resources\CarouselSlideResource\Pages;

use App\Filament\Resources\CarouselSlideResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarouselSlide extends EditRecord
{
    protected static string $resource = CarouselSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
