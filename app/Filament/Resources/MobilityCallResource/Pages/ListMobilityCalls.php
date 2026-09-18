<?php

namespace App\Filament\Resources\MobilityCallResource\Pages;

use App\Filament\Resources\MobilityCallResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMobilityCalls extends ListRecords
{
    protected static string $resource = MobilityCallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
