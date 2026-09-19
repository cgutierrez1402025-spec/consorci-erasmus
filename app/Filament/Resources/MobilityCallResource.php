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

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Convocatorias y Baremos';

    protected static ?string $modelLabel = 'Convocatoria de Selección';

    protected static ?string $pluralModelLabel = 'Convocatorias de Movilidad';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Definición de la Convocatoria')
                    ->schema([
                        Forms\Components\Select::make('erasmus_project_id')
                            ->label('Proyecto Erasmus+ Asociado')
                            ->relationship('project', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('Título de la Convocatoria')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Convocatoria FP Grado Medio - Prácticas Primavera 2025'),
                        Forms\Components\Select::make('mobility_type')
                            ->label('Tipo de Movilidad')
                            ->options([
                                MobilityType::VetStudentShort->value => MobilityType::VetStudentShort->getLabel(),
                                MobilityType::VetStudentLong->value => MobilityType::VetStudentLong->getLabel(),
                                MobilityType::VetBasic->value => MobilityType::VetBasic->getLabel(),
                                MobilityType::HigherVet->value => MobilityType::HigherVet->getLabel(),
                                MobilityType::StaffTraining->value => MobilityType::StaffTraining->getLabel(),
                            ])
                            ->required(),
                        Forms\Components\Select::make('program_type')
                            ->label('Programa de movilidad')
                            ->options([
                                'GS_ECHE' => 'Grado Superior - ECHE propia',
                                'GM_STEPV' => 'Grado Medio - Consorcio STEPV',
                                'STAFF' => 'Profesorado / personal',
                            ])
                            ->default('GM_STEPV')
                            ->required(),
                        Forms\Components\TextInput::make('academic_year')
                            ->label('Curso Académico')
                            ->default('2024-2025')
                            ->required(),
                        Forms\Components\TextInput::make('total_vacancies')
                            ->label('Plazas Ofertadas')
                            ->numeric()
                            ->default(10)
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Estado de la Convocatoria')
                            ->options([
                                'draft' => 'Borrador',
                                'open' => 'Abierta (Recepción solicitudes)',
                                'evaluating' => 'En baremación',
                                'resolved' => 'Resuelta / Adjudicada',
                                'closed' => 'Cerrada',
                            ])
                            ->default('open')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Plazos y Calendario')
                    ->schema([
                        Forms\Components\DatePicker::make('application_start_date')
                            ->label('Apertura de Solicitudes')
                            ->required(),
                        Forms\Components\DatePicker::make('application_end_date')
                            ->label('Cierre de Solicitudes')
                            ->required(),
                        Forms\Components\DatePicker::make('provisional_list_date')
                            ->label('Publicación Listado Provisional'),
                        Forms\Components\DatePicker::make('final_list_date')
                            ->label('Publicación Listado Definitivo'),
                        Forms\Components\Textarea::make('requirements')
                            ->label('Requisitos Específicos y Criterios de Selección')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Rúbrica de baremación')
                    ->description('Distribución inicial: expediente 30, idioma 20, equipo docente 20, entrevista 25 e inclusión 5 (máximo 100). El mínimo obligatorio para participar es 50 puntos.')
                    ->schema([
                        Forms\Components\TextInput::make('scoring_rubric.minimum_score')->label('Mínimo de participación')->numeric()->default(50)->minValue(50)->maxValue(50)->required(),
                        Forms\Components\TextInput::make('scoring_rubric.criteria.academic.maximum')->label('Máximo expediente')->numeric()->default(30)->minValue(0)->maxValue(100)->required(),
                        Forms\Components\TextInput::make('scoring_rubric.criteria.language.maximum')->label('Máximo idioma')->numeric()->default(20)->minValue(0)->maxValue(100)->required(),
                        Forms\Components\TextInput::make('scoring_rubric.criteria.faculty.maximum')->label('Máximo equipo docente')->numeric()->default(20)->minValue(0)->maxValue(100)->required(),
                        Forms\Components\TextInput::make('scoring_rubric.criteria.interview.maximum')->label('Máximo entrevista')->numeric()->default(25)->minValue(0)->maxValue(100)->required(),
                        Forms\Components\TextInput::make('scoring_rubric.criteria.inclusion.maximum')->label('Máximo inclusión')->numeric()->default(5)->minValue(0)->maxValue(100)->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Convocatoria')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('project.project_code')
                    ->label('Proyecto')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('mobility_type')
                    ->label('Modalidad')
                    ->formatStateUsing(fn ($state) => $state instanceof MobilityType ? $state->getLabel() : $state),
                Tables\Columns\TextColumn::make('total_vacancies')
                    ->label('Plazas')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('application_end_date')
                    ->label('Fin Solicitudes')
                    ->date('d/m/Y'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'open',
                        'warning' => 'evaluating',
                        'info' => 'resolved',
                        'gray' => 'draft',
                        'danger' => 'closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('prepareResolution')
                    ->label('Preparar resolución')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->action(function (MobilityCall $record) {
                        $blocked = collect((new ApplicationScoringService)->preparation($record))
                            ->filter(fn (array $item) => $item['blocks'] !== [])
                            ->map(fn (array $item) => "{$item['name']}: ".implode(', ', $item['blocks']))
                            ->implode("\n");
                        Notification::make()
                            ->title($blocked === '' ? 'Todas las candidaturas están preparadas' : 'Bloqueos detectados')
                            ->body($blocked === '' ? 'Puede proceder a resolver la convocatoria.' : $blocked)
                            ->{$blocked === '' ? 'success' : 'warning'}()
                            ->send();
                    }),
                Tables\Actions\Action::make('resolveCall')
                    ->label('Resolver y Asignar Plazas')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Adjudicación automática de plazas')
                    ->modalDescription("Esta acción ordenará las candidaturas presentadas por puntuación de baremo y asignará el estado 'Admitida' a los primeros según las plazas disponibles, dejando el resto en 'Lista de espera'.")
                    ->action(function (MobilityCall $record) {
                        $service = new ApplicationScoringService;
                        $result = $service->resolveCall($record);

                        Notification::make()
                            ->title('Convocatoria Resuelta')
                            ->body("Se han adjudicado {$result['admitted_count']} plazas y {$result['reserve_count']} candidaturas quedan en reserva.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('exportScoring')
                    ->label('Descargar CSV de baremación')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (MobilityCall $record) {
                        $filename = "baremacion-convocatoria-{$record->id}.csv";

                        return response()->streamDownload(function () use ($record): void {
                            $output = fopen('php://output', 'w');
                            fputcsv($output, ['Candidatura', 'Centro', 'Ciclo', 'Puntuación', 'Elegible', 'Estado', 'Faltas', 'Desempate'], ';');
                            foreach ($record->applications()->with('educationalCenter')->orderByDesc('total_score')->get() as $application) {
                                fputcsv($output, [
                                    $application->full_name,
                                    $application->educationalCenter?->name,
                                    $application->vocational_program,
                                    $application->total_score,
                                    $application->total_score >= $record->scoringRubric()['minimum_score'] ? 'Sí' : 'No',
                                    $application->status->getLabel(),
                                    $application->absence_count,
                                    $application->tie_break_reason,
                                ], ';');
                            }
                            fclose($output);
                        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMobilityCalls::route('/'),
            'create' => Pages\CreateMobilityCall::route('/create'),
            'edit' => Pages\EditMobilityCall::route('/{record}/edit'),
        ];
    }
}
