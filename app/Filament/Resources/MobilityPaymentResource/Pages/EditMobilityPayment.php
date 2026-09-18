<?php

namespace App\Filament\Resources\MobilityPaymentResource\Pages;

use App\Filament\Resources\MobilityPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMobilityPayment extends EditRecord
{
    protected static string $resource = MobilityPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
