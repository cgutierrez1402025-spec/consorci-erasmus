<?php

namespace App\Filament\Resources\MobilityResource\Pages;

use App\Filament\Resources\MobilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMobility extends EditRecord
{
    protected static string $resource = MobilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
