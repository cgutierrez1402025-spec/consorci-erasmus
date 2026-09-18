<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data["total_score"] = (float)($data["academic_score"] ?? 0)
            + (float)($data["language_score"] ?? 0)
            + (float)($data["faculty_report_score"] ?? 0)
            + (float)($data["interview_score"] ?? 0)
            + (float)($data["inclusion_factor_score"] ?? 0);
        return $data;
    }
}
