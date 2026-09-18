<?php

namespace App\Filament\Resources\EducationalCenterResource\Pages;

use App\Filament\Resources\EducationalCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEducationalCenters extends ListRecords
{
    protected static string $resource = EducationalCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
