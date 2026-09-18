<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestApplicationsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = "full";

    protected static ?string $heading = "Últimas Candidaturas Registradas";

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Application::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make("full_name")
                    ->label("Candidato/a")
                    ->weight("bold"),
                Tables\Columns\TextColumn::make("educationalCenter.name")
                    ->label("Centro")
                    ->limit(25),
                Tables\Columns\TextColumn::make("vocational_program")
                    ->label("Ciclo FP")
                    ->limit(30),
                Tables\Columns\TextColumn::make("language_certificate_level")
                    ->label("Nivel Idioma")
                    ->badge(),
                Tables\Columns\TextColumn::make("total_score")
                    ->label("Baremo Total")
                    ->alignCenter(),
                Tables\Columns\BadgeColumn::make("status")
                    ->label("Estado")
                    ->formatStateUsing(fn ($state) => $state instanceof ApplicationStatus ? $state->getLabel() : $state)
                    ->colors([
                        "gray" => ApplicationStatus::Submitted->value,
                        "warning" => ApplicationStatus::InReview->value,
                        "info" => ApplicationStatus::Scored->value,
                        "success" => ApplicationStatus::Admitted->value,
                    ]),
            ])
            ->paginated(false);
    }
}
