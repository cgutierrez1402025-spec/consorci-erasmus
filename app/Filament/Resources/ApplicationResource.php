<?php

namespace App\Filament\Resources;

use App\Enums\ApplicationStatus;
use App\Enums\MobilityStatus;
use App\Filament\Resources\ApplicationResource\Pages;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Mobility;
use App\Services\ApplicationScoringService;
use App\Services\GrantCalculationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Candidaturas';

    protected static ?string $modelLabel = 'Candidatura';

    protected static ?string $pluralModelLabel = 'Solicitudes y Baremos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Convocatoria y Centro Educativo')
                    ->schema([
                        Forms\Components\Select::make('mobility_call_id')
                            ->label('Convocatoria')
                            ->relationship('call', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('educational_center_id')
                            ->label('Centro Educativo de Origen')
                            ->relationship('educationalCenter', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Datos Personales del Candidato/a')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Apellidos')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\Select::make('id_document_type')
                            ->label('Tipo de Documento')
                            ->options([
                                'DNI' => 'DNI',
                                'NIE' => 'NIE',
                                'Pasaporte' => 'Pasaporte',
                            ])
                            ->default('DNI')
                            ->required(),
                        Forms\Components\TextInput::make('id_document_number')
                            ->label('Número de Documento')
                            ->required()
                            ->maxLength(30),
                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->required()
                            ->maxLength(30),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Fecha de Nacimiento'),
                        Forms\Components\TextInput::make('birth_place')->label('Lugar de nacimiento')->maxLength(255),
                        Forms\Components\TextInput::make('nationality')->label('Nacionalidad')->maxLength(100),
                        Forms\Components\TextInput::make('address')->label('Dirección')->maxLength(255),
                        Forms\Components\TextInput::make('locality')->label('Localidad')->maxLength(100),
                        Forms\Components\TextInput::make('province')->label('Provincia')->maxLength(100),
                        Forms\Components\TextInput::make('vocational_program')
                            ->label('Ciclo Formativo que cursa')
                            ->required()
                            ->placeholder('Técnico en Cuidados Auxiliares de Enfermería'),
                        Forms\Components\Select::make('level')
                            ->label('Nivel / Colectivo')
                            ->options([
                                'fp_basica' => 'FP Básica',
                                'grado_medio' => 'FP Grado Medio',
                                'grado_superior' => 'FP Grado Superior',
                                'docente' => 'Profesorado / Personal',
                            ])
                            ->default('grado_medio')
                            ->required(),
                        Forms\Components\Select::make('participant_role')->label('Rol de participante')->options([
                            'student' => 'Alumnado', 'accompanying_teacher' => 'Profesorado acompañante', 'job_shadowing_teacher' => 'Profesorado Job Shadowing',
                        ])->default('student')->required(),
                        Forms\Components\Toggle::make('is_minor')->label('Menor de edad')->default(false),
                        Forms\Components\Select::make('training_branch')->label('Rama formativa')->options([
                            'SOCIO_SANITARIA' => 'Socio-sanitaria', 'TECNOLOGICA' => 'Tecnológica', 'ADMINISTRATIVA' => 'Administrativa', 'OTRA' => 'Otra',
                        ]),
                    ])->columns(3),

                Forms\Components\Section::make('Preferencias y consentimiento')
                    ->schema([
                        Forms\Components\TagsInput::make('country_preferences')->label('Países preferentes')->separator(','),
                        Forms\Components\Toggle::make('communications_consent')->label('Acepta comunicaciones de proyectos Erasmus+')->required(),
                        Forms\Components\TextInput::make('iban')->label('IBAN (solo seleccionado/a)')->maxLength(34)->password()->revealable(),
                        Forms\Components\TextInput::make('bank_account_holder')->label('Titular bancario exacto')->maxLength(255),
                        Forms\Components\Textarea::make('fewer_opportunities_reason')->label('Motivo de menos oportunidades')->visible(fn (Get $get) => (bool) $get('inclusion_factor_score')),
                    ])->columns(2),

                Forms\Components\Section::make('Documentación obligatoria')
                    ->description('El certificado de delitos penales y sexuales se exige automáticamente cuando el ciclo es sociosanitario.')
                    ->schema([
                        Forms\Components\Repeater::make('documents')->relationship()
                            ->schema([
                                Forms\Components\Select::make('document_type')->label('Documento')->options(ApplicationDocument::labels())->required()->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                Forms\Components\Select::make('status')->label('Estado')->options([
                                    ApplicationDocument::STATUS_PENDING => 'Pendiente',
                                    ApplicationDocument::STATUS_PROVIDED => 'Aportado',
                                    ApplicationDocument::STATUS_VALIDATED => 'Validado',
                                    ApplicationDocument::STATUS_REJECTED => 'Rechazado',
                                ])->required(),
                                Forms\Components\FileUpload::make('file_path')->label('Archivo')->disk('local')->directory('application-documents')->visibility('private'),
                                Forms\Components\DateTimePicker::make('validated_at')->label('Fecha de validación')->requiredIf('status', ApplicationDocument::STATUS_VALIDATED),
                                Forms\Components\Select::make('validated_by')->label('Persona validadora')->relationship('validator', 'name')->searchable()->preload()->requiredIf('status', ApplicationDocument::STATUS_VALIDATED),
                                Forms\Components\Textarea::make('observations')->label('Observaciones'),
                            ])->columns(3)->defaultItems(0)->addable(false)->deletable(false),
                    ]),

                Forms\Components\Section::make('Incidencias disciplinarias')
                    ->schema([
                        Forms\Components\Repeater::make('disciplinaryIncidents')->relationship()
                            ->schema([
                                Forms\Components\Select::make('severity')->label('Gravedad')->options(['minor' => 'Leve', 'serious' => 'Grave', 'very_serious' => 'Muy grave'])->required(),
                                Forms\Components\DatePicker::make('occurred_at')->label('Fecha')->required(),
                                Forms\Components\Textarea::make('description')->label('Descripción')->required(),
                                Forms\Components\Textarea::make('sanction')->label('Sanción'),
                                Forms\Components\Select::make('status')->label('Estado')->options(['open' => 'Abierta', 'resolved' => 'Resuelta', 'dismissed' => 'Archivada'])->default('open')->required(),
                                Forms\Components\Toggle::make('excludes_from_resolution')->label('Excluye de la resolución'),
                            ])->columns(3),
                    ]),

                Forms\Components\Section::make('Baremo de Selección Erasmus+')
                    ->description('Criterios objetivos según normativa de la convocatoria del consorcio')
                    ->schema([
                        Forms\Components\Select::make('language_certificate_level')
                            ->label('Nivel Idioma Acreditado')
                            ->options([
                                'none' => 'Sin acreditar (0 ptos)',
                                'A2' => 'Nivel A2 (1.00 pto)',
                                'B1' => 'Nivel B1 (2.50 ptos)',
                                'B2' => 'Nivel B2 (3.50 ptos)',
                                'C1' => 'Nivel C1 (4.50 ptos)',
                                'C2' => 'Nivel C2 (5.00 ptos)',
                            ])
                            ->default('B1')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $pts = match ($state) {
                                    'A2' => 1.0,
                                    'B1' => 2.5,
                                    'B2' => 3.5,
                                    'C1' => 4.5,
                                    'C2' => 5.0,
                                    default => 0.0,
                                };
                                $set('language_score', $pts);
                            }),
                        Forms\Components\TextInput::make('language_score')
                            ->label('Puntos Idioma')
                            ->numeric()
                            ->default(2.5),
                        Forms\Components\TextInput::make('academic_score')
                            ->label('Expediente Académico (0-10)')
                            ->numeric()
                            ->default(7.0),
                        Forms\Components\TextInput::make('faculty_report_score')
                            ->label('Informe Equipo Educativo (0-5)')
                            ->numeric()
                            ->default(4.0),
                        Forms\Components\TextInput::make('interview_score')
                            ->label('Nota de entrevista (calculada)')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('absence_count')
                            ->label('Faltas de asistencia')
                            ->numeric()->minValue(0)->default(0),
                        Forms\Components\TextInput::make('relevant_language_grade')
                            ->label('Nota de idioma relevante del expediente (0-10)')
                            ->numeric()->minValue(0)->maxValue(10),
                        Forms\Components\TextInput::make('inclusion_factor_score')
                            ->label('Factor Inclusión / Menos Oportunidades (+1)')
                            ->numeric()
                            ->default(0.0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::recomputeTotal($set, $get)),
                        Forms\Components\TextInput::make('total_score')
                            ->label('PUNTUACIÓN TOTAL BAREMO (máx. 100)')
                            ->numeric()
                            ->readOnly()
                            ->extraInputAttributes(['class' => 'font-bold text-lg text-primary-600']),
                    ])->columns(3),

                Forms\Components\Section::make('Resolución y Documentación')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado de la Solicitud')
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
                        Forms\Components\Textarea::make('motivation_letter')
                            ->label('Extracto de Carta de Motivación / Intereses de prácticas')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Anotaciones de la Comisión de Selección')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Candidato/a')
                    ->formatStateUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->weight('bold')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('educationalCenter.name')
                    ->label('Centro')
                    ->limit(20)
                    ->searchable(),
                Tables\Columns\TextColumn::make('vocational_program')
                    ->label('Ciclo Formativo')
                    ->limit(25)
                    ->searchable(),
                Tables\Columns\TextColumn::make('language_certificate_level')
                    ->label('Nivel')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('total_score')
                    ->label('Puntos')
                    ->sortable()
                    ->weight('bold')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('absence_count')->label('Faltas')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('documentation_complete')
                    ->label('Documentación')->boolean()->state(fn (Application $record) => $record->documentationComplete()),
                Tables\Columns\TextColumn::make('interview.status')->label('Entrevista')->badge(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => $state instanceof ApplicationStatus ? $state->getLabel() : $state)
                    ->colors([
                        'gray' => ApplicationStatus::Submitted->value,
                        'warning' => ApplicationStatus::InReview->value,
                        'info' => ApplicationStatus::Scored->value,
                        'success' => ApplicationStatus::Admitted->value,
                        'warning' => ApplicationStatus::Reserve->value,
                        'danger' => ApplicationStatus::Rejected->value,
                        'secondary' => ApplicationStatus::Withdrawn->value,
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('educational_center_id')
                    ->label('Centro')
                    ->relationship('educationalCenter', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        ApplicationStatus::Submitted->value => 'Presentada',
                        ApplicationStatus::DocumentationPending->value => 'Documentación pendiente',
                        ApplicationStatus::DocumentationComplete->value => 'Documentación completa',
                        ApplicationStatus::InterviewPending->value => 'Entrevista pendiente',
                        ApplicationStatus::InterviewCompleted->value => 'Entrevista realizada',
                        ApplicationStatus::Scored->value => 'Baremada',
                        ApplicationStatus::Ineligible->value => 'No elegible',
                        ApplicationStatus::Admitted->value => 'Admitida',
                        ApplicationStatus::Reserve->value => 'Reserva',
                        ApplicationStatus::Rejected->value => 'Rechazada',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('scoreAction')
                    ->label('Recalcular')
                    ->icon('heroicon-o-calculator')
                    ->action(function (Application $record) {
                        try {
                            (new ApplicationScoringService)->scoreApplication($record);
                        } catch (\DomainException $exception) {
                            Notification::make()->title('Baremo bloqueado')->body($exception->getMessage())->danger()->send();

                            return;
                        }
                        Notification::make()
                            ->title('Baremo Calculado')
                            ->body("Puntuación total: {$record->total_score} ptos.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('interview')
                    ->label('Entrevista')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (Application $record) => $record->interview
                        ? ApplicationInterviewResource::getUrl('edit', ['record' => $record->interview])
                        : ApplicationInterviewResource::getUrl('create', ['application' => $record->id])),
                Tables\Actions\Action::make('convertToMobility')
                    ->label('Crear Movilidad')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Application $record) => $record->status === ApplicationStatus::Admitted && ! $record->mobility)
                    ->requiresConfirmation()
                    ->action(function (Application $record) {
                        $calc = new GrantCalculationService;
                        $breakdown = $calc->calculate('IT', 30, 'standard', false, 'student');

                        $mobility = Mobility::create([
                            'erasmus_project_id' => $record->call->erasmus_project_id,
                            'mobility_call_id' => $record->mobility_call_id,
                            'application_id' => $record->id,
                            'educational_center_id' => $record->educational_center_id,
                            'participant_name' => $record->full_name,
                            'participant_email' => $record->email,
                            'participant_type' => 'student',
                            'destination_country' => 'IT',
                            'destination_city' => 'Roma',
                            'start_date' => now()->addDays(30),
                            'end_date' => now()->addDays(60),
                            'duration_days' => 30,
                            'travel_type' => 'standard',
                            'fewer_opportunities' => false,
                            'daily_grant_rate' => $breakdown['daily_rate'],
                            'individual_support_amount' => $breakdown['individual_support'],
                            'travel_amount' => $breakdown['travel_amount'],
                            'inclusion_amount' => 0,
                            'total_grant_amount' => $breakdown['total_grant'],
                            'status' => MobilityStatus::Planned,
                        ]);

                        Notification::make()
                            ->title('Movilidad Creada')
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
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
