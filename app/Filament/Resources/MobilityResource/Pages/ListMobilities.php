<?php

namespace App\Filament\Resources\MobilityResource\Pages;

use App\Filament\Resources\MobilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMobilities extends ListRecords
{
    protected static string $resource = MobilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
