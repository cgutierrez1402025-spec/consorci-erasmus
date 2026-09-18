<?php

namespace App\Filament\Resources;

use App\Enums\ApplicationStatus;
use App\Filament\Resources\ApplicationResource\Pages;
use App\Models\Application;
use App\Models\Mobility;
use App\Services\ApplicationScoringService;
use App\Services\GrantCalculationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = "heroicon-o-document-text";

    protected static ?string $navigationGroup = "Candidaturas";

    protected static ?string $modelLabel = "Candidatura";

    protected static ?string $pluralModelLabel = "Solicitudes y Baremos";

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("Convocatoria y Centro Educativo")
                    ->schema([
                        Forms\Components\Select::make("mobility_call_id")
                            ->label("Convocatoria")
                            ->relationship("call", "title")
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make("educational_center_id")
                            ->label("Centro Educativo de Origen")
                            ->relationship("educationalCenter", "name")
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make("Datos Personales del Candidato/a")
                    ->schema([
                        Forms\Components\TextInput::make("first_name")
                            ->label("Nombre")
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make("last_name")
                            ->label("Apellidos")
                            ->required()
                            ->maxLength(150),
                        Forms\Components\Select::make("id_document_type")
                            ->label("Tipo de Documento")
                            ->options([
                                "DNI" => "DNI",
                                "NIE" => "NIE",
                                "Pasaporte" => "Pasaporte",
                            ])
                            ->default("DNI")
                            ->required(),
                        Forms\Components\TextInput::make("id_document_number")
                            ->label("Número de Documento")
                            ->required()
                            ->maxLength(30),
                        Forms\Components\TextInput::make("email")
                            ->label("Correo Electrónico")
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("phone")
                            ->label("Teléfono")
                            ->tel()
                            ->required()
                            ->maxLength(30),
                        Forms\Components\DatePicker::make("birth_date")
                            ->label("Fecha de Nacimiento"),
                        Forms\Components\TextInput::make("vocational_program")
                            ->label("Ciclo Formativo que cursa")
                            ->required()
                            ->placeholder("Técnico en Cuidados Auxiliares de Enfermería"),
                        Forms\Components\Select::make("level")
                            ->label("Nivel / Colectivo")
                            ->options([
                                "fp_basica" => "FP Básica",
                                "grado_medio" => "FP Grado Medio",
                                "grado_superior" => "FP Grado Superior",
                                "docente" => "Profesorado / Personal",
                            ])
                            ->default("grado_medio")
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make("Baremo de Selección Erasmus+")
                    ->description("Criterios objetivos según normativa de la convocatoria del consorcio")
                    ->schema([
                        Forms\Components\Select::make("language_certificate_level")
                            ->label("Nivel Idioma Acreditado")
                            ->options([
                                "none" => "Sin acreditar (0 ptos)",
                                "A2" => "Nivel A2 (1.00 pto)",
                                "B1" => "Nivel B1 (2.50 ptos)",
                                "B2" => "Nivel B2 (3.50 ptos)",
                                "C1" => "Nivel C1 (4.50 ptos)",
                                "C2" => "Nivel C2 (5.00 ptos)",
                            ])
                            ->default("B1")
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $pts = match ($state) {
                                    "A2" => 1.0,
                                    "B1" => 2.5,
                                    "B2" => 3.5,
                                    "C1" => 4.5,
                                    "C2" => 5.0,
                                    default => 0.0,
                                };
                                $set("language_score", $pts);
                                self::recomputeTotal($set, $get);
                            }),
                        Forms\Components\TextInput::make("language_score")
                            ->label("Puntos Idioma")
                            ->numeric()
                            ->default(2.5)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::recomputeTotal($set, $get)),
                        Forms\Components\TextInput::make("academic_score")
                            ->label("Expediente Académico (0-10)")
                            ->numeric()
                            ->default(7.0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::recomputeTotal($set, $get)),
                        Forms\Components\TextInput::make("faculty_report_score")
                            ->label("Informe Equipo Educativo (0-5)")
                            ->numeric()
                            ->default(4.0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::recomputeTotal($set, $get)),
                        Forms\Components\TextInput::make("interview_score")
                            ->label("Entrevista / Motivación (0-5)")
                            ->numeric()
                            ->default(4.0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::recomputeTotal($set, $get)),
                        Forms\Components\TextInput::make("inclusion_factor_score")
                            ->label("Factor Inclusión / Menos Oportunidades (+1)")
                            ->numeric()
                            ->default(0.0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::recomputeTotal($set, $get)),
                        Forms\Components\TextInput::make("total_score")
                            ->label("PUNTUACIÓN TOTAL BAREMO")
                            ->numeric()
                            ->readOnly()
                            ->extraInputAttributes(["class" => "font-bold text-lg text-primary-600"]),
                    ])->columns(3),

                Forms\Components\Section::make("Resolución y Documentación")
                    ->schema([
                        Forms\Components\Select::make("status")
                            ->label("Estado de la Solicitud")
                            ->options([
                                ApplicationStatus::Submitted->value => ApplicationStatus::Submitted->getLabel(),
                                ApplicationStatus::InReview->value => ApplicationStatus::InReview->getLabel(),
                                ApplicationStatus::Scored->value => ApplicationStatus::Scored->getLabel(),
                                ApplicationStatus::Admitted->value => ApplicationStatus::Admitted->getLabel(),
                                ApplicationStatus::Reserve->value => ApplicationStatus::Reserve->getLabel(),
                                ApplicationStatus::Rejected->value => ApplicationStatus::Rejected->getLabel(),
                                ApplicationStatus::Withdrawn->value => ApplicationStatus::Withdrawn->getLabel(),
                            ])
                            ->default(ApplicationStatus::Submitted->value)
                            ->required(),
                        Forms\Components\Textarea::make("motivation_letter")
                            ->label("Extracto de Carta de Motivación / Intereses de prácticas")
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make("notes")
                            ->label("Anotaciones de la Comisión de Selección")
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function recomputeTotal(Set $set, Get $get): void
    {
        $total = (float) $get("academic_score")
            + (float) $get("language_score")
            + (float) $get("faculty_report_score")
            + (float) $get("interview_score")
            + (float) $get("inclusion_factor_score");

        $set("total_score", round($total, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("first_name")
                    ->label("Candidato/a")
                    ->formatStateUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->weight("bold")
                    ->searchable(["first_name", "last_name"]),
                Tables\Columns\TextColumn::make("educationalCenter.name")
                    ->label("Centro")
                    ->limit(20)
                    ->searchable(),
                Tables\Columns\TextColumn::make("vocational_program")
                    ->label("Ciclo Formativo")
                    ->limit(25)
                    ->searchable(),
                Tables\Columns\TextColumn::make("language_certificate_level")
                    ->label("Nivel")
                    ->badge()
                    ->color("info"),
                Tables\Columns\TextColumn::make("total_score")
                    ->label("Puntos")
                    ->sortable()
                    ->weight("bold")
                    ->alignCenter(),
                Tables\Columns\BadgeColumn::make("status")
                    ->label("Estado")
                    ->formatStateUsing(fn ($state) => $state instanceof ApplicationStatus ? $state->getLabel() : $state)
                    ->colors([
                        "gray" => ApplicationStatus::Submitted->value,
                        "warning" => ApplicationStatus::InReview->value,
                        "info" => ApplicationStatus::Scored->value,
                        "success" => ApplicationStatus::Admitted->value,
                        "warning" => ApplicationStatus::Reserve->value,
                        "danger" => ApplicationStatus::Rejected->value,
                        "secondary" => ApplicationStatus::Withdrawn->value,
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("educational_center_id")
                    ->label("Centro")
                    ->relationship("educationalCenter", "name"),
                Tables\Filters\SelectFilter::make("status")
                    ->label("Estado")
                    ->options([
                        ApplicationStatus::Submitted->value => "Presentada",
                        ApplicationStatus::Scored->value => "Baremada",
                        ApplicationStatus::Admitted->value => "Admitida",
                        ApplicationStatus::Reserve->value => "Reserva",
                        ApplicationStatus::Rejected->value => "Rechazada",
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make("scoreAction")
                    ->label("Recalcular")
                    ->icon("heroicon-o-calculator")
                    ->action(function (Application $record) {
                        (new ApplicationScoringService())->scoreApplication($record);
                        Notification::make()
                            ->title("Baremo Calculado")
                            ->body("Puntuación total: {$record->total_score} ptos.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make("convertToMobility")
                    ->label("Crear Movilidad")
                    ->icon("heroicon-o-paper-airplane")
                    ->color("success")
                    ->visible(fn (Application $record) => $record->status === ApplicationStatus::Admitted && !$record->mobility)
                    ->requiresConfirmation()
                    ->action(function (Application $record) {
                        $calc = new GrantCalculationService();
                        $breakdown = $calc->calculate("IT", 30, "standard", false, "student");

                        $mobility = Mobility::create([
                            "erasmus_project_id" => $record->call->erasmus_project_id,
                            "mobility_call_id" => $record->mobility_call_id,
                            "application_id" => $record->id,
                            "educational_center_id" => $record->educational_center_id,
                            "participant_name" => $record->full_name,
                            "participant_email" => $record->email,
                            "participant_type" => "student",
                            "destination_country" => "IT",
                            "destination_city" => "Roma",
                            "start_date" => now()->addDays(30),
                            "end_date" => now()->addDays(60),
                            "duration_days" => 30,
                            "travel_type" => "standard",
                            "fewer_opportunities" => false,
                            "daily_grant_rate" => $breakdown["daily_rate"],
                            "individual_support_amount" => $breakdown["individual_support"],
                            "travel_amount" => $breakdown["travel_amount"],
                            "inclusion_amount" => 0,
                            "total_grant_amount" => $breakdown["total_grant"],
                            "status" => \App\Enums\MobilityStatus::Planned,
                        ]);

                        Notification::make()
                            ->title("Movilidad Creada")
                            ->body("Se ha generado la estancia para {$record->full_name} con subvención calculada de {$mobility->total_grant_amount}€.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListApplications::route("/"),
            "create" => Pages\CreateApplication::route("/create"),
            "edit" => Pages\EditApplication::route("/{record}/edit"),
        ];
    }
}
