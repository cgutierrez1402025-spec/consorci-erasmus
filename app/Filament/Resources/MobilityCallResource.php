<?php

namespace App\Filament\Resources;

use App\Enums\MobilityType;
use App\Filament\Resources\MobilityCallResource\Pages;
use App\Models\MobilityCall;
use App\Services\ApplicationScoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MobilityCallResource extends Resource
{
    protected static ?string $model = MobilityCall::class;

    protected static ?string $navigationIcon = "heroicon-o-megaphone";

    protected static ?string $navigationGroup = "Convocatorias y Baremos";

    protected static ?string $modelLabel = "Convocatoria de Selección";

    protected static ?string $pluralModelLabel = "Convocatorias de Movilidad";

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("Definición de la Convocatoria")
                    ->schema([
                        Forms\Components\Select::make("erasmus_project_id")
                            ->label("Proyecto Erasmus+ Asociado")
                            ->relationship("project", "title")
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make("title")
                            ->label("Título de la Convocatoria")
                            ->required()
                            ->maxLength(255)
                            ->placeholder("Convocatoria FP Grado Medio - Prácticas Primavera 2025"),
                        Forms\Components\Select::make("mobility_type")
                            ->label("Tipo de Movilidad")
                            ->options([
                                MobilityType::VetStudentShort->value => MobilityType::VetStudentShort->getLabel(),
                                MobilityType::VetStudentLong->value => MobilityType::VetStudentLong->getLabel(),
                                MobilityType::VetBasic->value => MobilityType::VetBasic->getLabel(),
                                MobilityType::HigherVet->value => MobilityType::HigherVet->getLabel(),
                                MobilityType::StaffTraining->value => MobilityType::StaffTraining->getLabel(),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make("academic_year")
                            ->label("Curso Académico")
                            ->default("2024-2025")
                            ->required(),
                        Forms\Components\TextInput::make("total_vacancies")
                            ->label("Plazas Ofertadas")
                            ->numeric()
                            ->default(10)
                            ->required(),
                        Forms\Components\Select::make("status")
                            ->label("Estado de la Convocatoria")
                            ->options([
                                "draft" => "Borrador",
                                "open" => "Abierta (Recepción solicitudes)",
                                "evaluating" => "En baremación",
                                "resolved" => "Resuelta / Adjudicada",
                                "closed" => "Cerrada",
                            ])
                            ->default("open")
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make("Plazos y Calendario")
                    ->schema([
                        Forms\Components\DatePicker::make("application_start_date")
                            ->label("Apertura de Solicitudes")
                            ->required(),
                        Forms\Components\DatePicker::make("application_end_date")
                            ->label("Cierre de Solicitudes")
                            ->required(),
                        Forms\Components\DatePicker::make("provisional_list_date")
                            ->label("Publicación Listado Provisional"),
                        Forms\Components\DatePicker::make("final_list_date")
                            ->label("Publicación Listado Definitivo"),
                        Forms\Components\Textarea::make("requirements")
                            ->label("Requisitos Específicos y Criterios de Selección")
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("title")
                    ->label("Convocatoria")
                    ->weight("bold")
                    ->searchable(),
                Tables\Columns\TextColumn::make("project.project_code")
                    ->label("Proyecto")
                    ->badge()
                    ->color("gray"),
                Tables\Columns\TextColumn::make("mobility_type")
                    ->label("Modalidad")
                    ->formatStateUsing(fn ($state) => $state instanceof MobilityType ? $state->getLabel() : $state),
                Tables\Columns\TextColumn::make("total_vacancies")
                    ->label("Plazas")
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make("application_end_date")
                    ->label("Fin Solicitudes")
                    ->date("d/m/Y"),
                Tables\Columns\BadgeColumn::make("status")
                    ->label("Estado")
                    ->colors([
                        "success" => "open",
                        "warning" => "evaluating",
                        "info" => "resolved",
                        "gray" => "draft",
                        "danger" => "closed",
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make("resolveCall")
                    ->label("Resolver y Asignar Plazas")
                    ->icon("heroicon-o-check-badge")
                    ->color("success")
                    ->requiresConfirmation()
                    ->modalHeading("Adjudicación automática de plazas")
                    ->modalDescription("Esta acción ordenará las candidaturas presentadas por puntuación de baremo y asignará el estado 'Admitida' a los primeros según las plazas disponibles, dejando el resto en 'Lista de espera'.")
                    ->action(function (MobilityCall $record) {
                        $service = new ApplicationScoringService();
                        $result = $service->resolveCall($record);

                        Notification::make()
                            ->title("Convocatoria Resuelta")
                            ->body("Se han adjudicado {$result['admitted_count']} plazas y {$result['reserve_count']} candidaturas quedan en reserva.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListMobilityCalls::route("/"),
            "create" => Pages\CreateMobilityCall::route("/create"),
            "edit" => Pages\EditMobilityCall::route("/{record}/edit"),
        ];
    }
}
