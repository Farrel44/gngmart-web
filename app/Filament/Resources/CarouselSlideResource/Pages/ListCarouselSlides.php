<?php

namespace App\Filament\Resources\CarouselSlideResource\Pages;

use App\Filament\Resources\CarouselSlideResource;
use Filament\Resources\Pages\ListRecords;

class ListCarouselSlides extends ListRecords
{
    protected static string $resource = CarouselSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
