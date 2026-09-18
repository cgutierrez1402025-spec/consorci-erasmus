<?php

namespace App\Filament\Resources;

use App\Enums\MobilityStatus;
use App\Filament\Resources\MobilityResource\Pages;
use App\Models\Mobility;
use App\Models\MobilityPayment;
use App\Services\GrantCalculationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MobilityResource extends Resource
{
    protected static ?string $model = Mobility::class;

    protected static ?string $navigationIcon = "heroicon-o-globe-europe-africa";

    protected static ?string $navigationGroup = "Movilidades y Estancias";

    protected static ?string $modelLabel = "Movilidad / Estancia";

    protected static ?string $pluralModelLabel = "Movilidades Erasmus+";

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("Participante y Proyecto")
                    ->schema([
                        Forms\Components\Select::make("erasmus_project_id")
                            ->label("Proyecto SEPIE")
                            ->relationship("project", "title")
                            ->searchable()
                            ->preload()
                            ->required(),
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
                        Forms\Components\Select::make("host_partner_id")
                            ->label("Empresa de Destino / Socio Europeo")
                            ->relationship("hostPartner", "name")
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make("participant_name")
                            ->label("Nombre del Participante")
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("participant_email")
                            ->label("Email del Participante")
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make("participant_type")
                            ->label("Tipo de Participante")
                            ->options([
                                "student" => "Estudiante FP",
                                "staff" => "Profesorado / Personal",
                            ])
                            ->default("student")
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make("Fechas y Destino de la Estancia")
                    ->schema([
                        Forms\Components\TextInput::make("destination_country")
                            ->label("País de Destino (DE, IT, IE, FR, PT...)")
                            ->required()
                            ->maxLength(5)
                            ->live(),
                        Forms\Components\TextInput::make("destination_city")
                            ->label("Ciudad de Destino")
                            ->required()
                            ->maxLength(100),
                        Forms\Components\DatePicker::make("start_date")
                            ->label("Fecha Inicio")
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateDuration($set, $get)),
                        Forms\Components\DatePicker::make("end_date")
                            ->label("Fecha Fin")
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateDuration($set, $get)),
                        Forms\Components\TextInput::make("duration_days")
                            ->label("Duración Total (Días)")
                            ->numeric()
                            ->required(),
                        Forms\Components\Select::make("travel_type")
                            ->label("Tipo de Desplazamiento")
                            ->options([
                                "standard" => "Viaje Estándar (Avión)",
                                "green_travel" => "Viaje Ecológico (Green Travel: Tren/Bus)",
                            ])
                            ->default("standard")
                            ->required(),
                        Forms\Components\Toggle::make("fewer_opportunities")
                            ->label("Menos Oportunidades / Complemento Inclusión")
                            ->default(false),
                    ])->columns(3),

                Forms\Components\Section::make("Financiación y Cuantía de la Beca SEPIE")
                    ->schema([
                        Forms\Components\TextInput::make("daily_grant_rate")
                            ->label("Dieta Diaria (€/día)")
                            ->numeric()
                            ->prefix("€")
                            ->required(),
                        Forms\Components\TextInput::make("individual_support_amount")
                            ->label("Apoyo Individual Total (€)")
                            ->numeric()
                            ->prefix("€")
                            ->required(),
                        Forms\Components\TextInput::make("travel_amount")
                            ->label("Ayuda de Viaje (€)")
                            ->numeric()
                            ->prefix("€")
                            ->required(),
                        Forms\Components\TextInput::make("inclusion_amount")
                            ->label("Suplemento Inclusión (€)")
                            ->numeric()
                            ->prefix("€")
                            ->default(0),
                        Forms\Components\TextInput::make("total_grant_amount")
                            ->label("TOTAL BECA CONCEDIDA (€)")
                            ->numeric()
                            ->prefix("€")
                            ->required()
                            ->extraInputAttributes(["class" => "font-bold text-lg text-primary-600"]),
                        Forms\Components\Select::make("status")
                            ->label("Estado de la Movilidad")
                            ->options([
                                MobilityStatus::Planned->value => MobilityStatus::Planned->getLabel(),
                                MobilityStatus::Contracted->value => MobilityStatus::Contracted->getLabel(),
                                MobilityStatus::InProgress->value => MobilityStatus::InProgress->getLabel(),
                                MobilityStatus::Completed->value => MobilityStatus::Completed->getLabel(),
                                MobilityStatus::Cancelled->value => MobilityStatus::Cancelled->getLabel(),
                            ])
                            ->default(MobilityStatus::Planned->value)
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make("Documentación Obligatoria y Cumplimiento")
                    ->schema([
                        Forms\Components\Toggle::make("learning_agreement_signed")
                            ->label("Learning Agreement Firmado (Acuerdo de Aprendizaje)")
                            ->default(false),
                        Forms\Components\Toggle::make("grant_agreement_signed")
                            ->label("Convenio de Subvención Firmado")
                            ->default(false),
                        Forms\Components\Toggle::make("certificate_of_attendance")
                            ->label("Certificado de Estancia de la Empresa (Attendance)")
                            ->default(false),
                        Forms\Components\Toggle::make("eu_survey_completed")
                            ->label("Encuesta Final UE (EU Survey) Cumplimentada")
                            ->default(false),
                        Forms\Components\TextInput::make("tutor_in_origin")
                            ->label("Tutor/a del Centro de Origen"),
                        Forms\Components\TextInput::make("tutor_in_host")
                            ->label("Tutor/a de la Empresa de Destino"),
                        Forms\Components\Textarea::make("notes")
                            ->label("Incidencias y Seguimiento")
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    private static function calculateDuration(Set $set, Get $get): void
    {
        $start = $get("start_date");
        $end = $get("end_date");
        if ($start && $end) {
            $days = (strtotime($end) - strtotime($start)) / (60 * 60 * 24);
            if ($days > 0) {
                $set("duration_days", (int)$days);
                $country = $get("destination_country") ?: "IT";
                $travelType = $get("travel_type") ?: "standard";
                $fewer = (bool)$get("fewer_opportunities");
                $type = $get("participant_type") ?: "student";

                $calc = (new GrantCalculationService())->calculate($country, (int)$days, $travelType, $fewer, $type);
                $set("daily_grant_rate", $calc["daily_rate"]);
                $set("individual_support_amount", $calc["individual_support"]);
                $set("travel_amount", $calc["travel_amount"]);
                $set("inclusion_amount", $calc["inclusion_amount"]);
                $set("total_grant_amount", $calc["total_grant"]);
            }
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("participant_name")
                    ->label("Participante")
                    ->weight("bold")
                    ->searchable(),
                Tables\Columns\TextColumn::make("destination_country")
                    ->label("País")
                    ->badge(),
                Tables\Columns\TextColumn::make("destination_city")
                    ->label("Ciudad")
                    ->searchable(),
                Tables\Columns\TextColumn::make("hostPartner.name")
                    ->label("Empresa Acogida")
                    ->limit(20)
                    ->placeholder("Por asignar"),
                Tables\Columns\TextColumn::make("start_date")
                    ->label("Salida")
                    ->date("d/m/Y")
                    ->sortable(),
                Tables\Columns\TextColumn::make("duration_days")
                    ->label("Días")
                    ->alignCenter(),
                Tables\Columns\TextColumn::make("total_grant_amount")
                    ->label("Beca Total")
                    ->money("EUR")
                    ->sortable(),
                Tables\Columns\BadgeColumn::make("status")
                    ->label("Estado")
                    ->formatStateUsing(fn ($state) => $state instanceof MobilityStatus ? $state->getLabel() : $state)
                    ->colors([
                        "gray" => MobilityStatus::Planned->value,
                        "info" => MobilityStatus::Contracted->value,
                        "warning" => MobilityStatus::InProgress->value,
                        "success" => MobilityStatus::Completed->value,
                        "danger" => MobilityStatus::Cancelled->value,
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("destination_country")
                    ->label("País Destino"),
                Tables\Filters\SelectFilter::make("status")
                    ->label("Estado"),
            ])
            ->actions([
                Tables\Actions\Action::make("generatePayments")
                    ->label("Generar Pagos 80% / 20%")
                    ->icon("heroicon-o-banknotes")
                    ->color("success")
                    ->requiresConfirmation()
                    ->modalHeading("Programar Pagos de Subvención Erasmus+")
                    ->modalDescription("Generará dos registros de pago: 80% inicial como anticipo previo a la salida y 20% liquidación final condicionada a la entrega del Certificado de Estancia y EU Survey.")
                    ->action(function (Mobility $record) {
                        $p1 = round($record->total_grant_amount * 0.80, 2);
                        $p2 = round($record->total_grant_amount - $p1, 2);

                        // Delete existing pending payments
                        $record->payments()->where("status", "pending")->delete();

                        MobilityPayment::create([
                            "mobility_id" => $record->id,
                            "payment_type" => "first_payment_80",
                            "amount" => $p1,
                            "scheduled_date" => $record->start_date ? $record->start_date->copy()->subDays(10) : now(),
                            "status" => \App\Enums\PaymentStatus::Pending,
                            "reference_number" => "ANT80-" . $record->id . "-" . date("Ymd"),
                            "notes" => "Anticipo del 80% tras firma del Convenio de Subvención",
                        ]);

                        MobilityPayment::create([
                            "mobility_id" => $record->id,
                            "payment_type" => "final_balance_20",
                            "amount" => $p2,
                            "scheduled_date" => $record->end_date ? $record->end_date->copy()->addDays(15) : now()->addDays(30),
                            "status" => \App\Enums\PaymentStatus::Pending,
                            "reference_number" => "LIQ20-" . $record->id . "-" . date("Ymd"),
                            "notes" => "Liquidación final del 20% tras presentación de Certificado de Estancia y encuesta EU Survey",
                        ]);

                        Notification::make()
                            ->title("Pagos Programados")
                            ->body("Anticipo 80% ({$p1}€) y Liquidación 20% ({$p2}€) creados correctamente.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListMobilities::route("/"),
            "create" => Pages\CreateMobility::route("/create"),
            "edit" => Pages\EditMobility::route("/{record}/edit"),
        ];
    }
}
