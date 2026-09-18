<?php

namespace App\Filament\Resources\HostPartnerResource\Pages;

use App\Filament\Resources\HostPartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHostPartners extends ListRecords
{
    protected static string $resource = HostPartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
