<?php

namespace App\Filament\Resources\ErasmusProjectResource\Pages;

use App\Filament\Resources\ErasmusProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListErasmusProjects extends ListRecords
{
    protected static string $resource = ErasmusProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
