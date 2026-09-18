<?php

namespace App\Filament\Resources\ApplicationInterviewResource\Pages;

use App\Filament\Resources\ApplicationInterviewResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApplicationInterview extends CreateRecord
{
    protected static string $resource = ApplicationInterviewResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['application_id'] ??= request()->integer('application');

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncApplicationScore();
    }
}
