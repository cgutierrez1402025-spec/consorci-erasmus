<?php

namespace App\Filament\Resources\EducationalCenterResource\Pages;

use App\Filament\Resources\EducationalCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEducationalCenter extends EditRecord
{
    protected static string $resource = EducationalCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
