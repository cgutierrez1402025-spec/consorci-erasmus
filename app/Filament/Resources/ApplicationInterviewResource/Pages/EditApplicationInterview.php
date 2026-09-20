<?php

namespace App\Filament\Resources\ApplicationInterviewResource\Pages;

use App\Filament\Resources\ApplicationInterviewResource;
use Filament\Resources\Pages\EditRecord;

class EditApplicationInterview extends EditRecord
{
    protected static string $resource = ApplicationInterviewResource::class;

    protected function afterSave(): void
    {
        $this->record->syncApplicationScore();
    }
}
