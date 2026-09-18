<?php

namespace App\Filament\Resources\ErasmusProjectResource\Pages;

use App\Filament\Resources\ErasmusProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditErasmusProject extends EditRecord
{
    protected static string $resource = ErasmusProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
