<?php

namespace App\Filament\Widgets;

use App\Enums\MobilityStatus;
use App\Models\Mobility;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActiveMobilitiesWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = "full";

    protected static ?string $heading = "Estancias Erasmus+ en Curso y Próximas Salidas";

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Mobility::query()
                    ->whereIn("status", [MobilityStatus::InProgress, MobilityStatus::Contracted, MobilityStatus::Planned])
                    ->latest("start_date")
                    ->limit(6)
            )
            ->columns([
                Tables\Columns\TextColumn::make("participant_name")
                    ->label("Participante")
                    ->weight("bold"),
                Tables\Columns\TextColumn::make("destination_country")
                    ->label("País")
                    ->badge(),
                Tables\Columns\TextColumn::make("destination_city")
                    ->label("Ciudad"),
                Tables\Columns\TextColumn::make("hostPartner.name")
                    ->label("Empresa Acogida")
                    ->placeholder("En asignación"),
                Tables\Columns\TextColumn::make("start_date")
                    ->label("Fecha Salida")
                    ->date("d/m/Y"),
                Tables\Columns\TextColumn::make("duration_days")
                    ->label("Días"),
                Tables\Columns\TextColumn::make("total_grant_amount")
                    ->label("Beca Total")
                    ->money("EUR"),
                Tables\Columns\BadgeColumn::make("status")
                    ->label("Estado")
                    ->formatStateUsing(fn ($state) => $state instanceof MobilityStatus ? $state->getLabel() : $state)
                    ->colors([
                        "gray" => MobilityStatus::Planned->value,
                        "info" => MobilityStatus::Contracted->value,
                        "warning" => MobilityStatus::InProgress->value,
                    ]),
            ])
            ->paginated(false);
    }
}
