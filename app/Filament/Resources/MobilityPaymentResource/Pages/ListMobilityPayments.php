<?php

namespace App\Filament\Resources\MobilityPaymentResource\Pages;

use App\Filament\Resources\MobilityPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMobilityPayments extends ListRecords
{
    protected static string $resource = MobilityPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
