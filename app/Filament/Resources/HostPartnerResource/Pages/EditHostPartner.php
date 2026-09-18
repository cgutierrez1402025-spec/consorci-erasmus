<?php

namespace App\Filament\Resources\HostPartnerResource\Pages;

use App\Filament\Resources\HostPartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHostPartner extends EditRecord
{
    protected static string $resource = HostPartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
