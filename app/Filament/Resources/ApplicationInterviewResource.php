<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationInterviewResource\Pages;
use App\Models\ApplicationInterview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationInterviewResource extends Resource
{
    protected static ?string $model = ApplicationInterview::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Candidaturas';

    protected static ?string $modelLabel = 'Entrevista';

    protected static ?string $pluralModelLabel = 'Entrevistas de alumnado';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Registro de entrevista')->schema([
                Forms\Components\Select::make('application_id')->label('Candidatura')->relationship('application', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)->searchable()->preload()->required()->default(fn () => request()->integer('application'))->unique(ignoreRecord: true),
                Forms\Components\DatePicker::make('interviewed_at')->label('Fecha')->required(),
                Forms\Components\Select::make('evaluator_id')->label('Persona evaluadora')->relationship('evaluator', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('status')->label('Estado')->options([
                    ApplicationInterview::STATUS_PENDING => 'Pendiente',
                    ApplicationInterview::STATUS_COMPLETED => 'Realizada',
                    ApplicationInterview::STATUS_VALIDATED => 'Validada',
                ])->required()->default(ApplicationInterview::STATUS_PENDING),
            ])->columns(2),
            Forms\Components\Section::make('Respuestas')->schema([
                Forms\Components\Textarea::make('certified_languages')->label('Idiomas certificados')->required(),
                Forms\Components\Textarea::make('selection_reason')->label('Por qué elegir a la persona candidata')->required(),
                Forms\Components\TagsInput::make('preferred_countries')->label('Países preferentes')->separator(','),
                Forms\Components\Textarea::make('observations')->label('Observaciones'),
                Forms\Components\Textarea::make('relevant_information')->label('Información relevante'),
            ])->columns(2),
            Forms\Components\Section::make('Criterios evaluables')->description('Cada criterio se puntúa de 0 a 10 e incluye comentario. La nota de entrevista se calcula como la media y se pondera con la rúbrica de la convocatoria.')->schema([
                Forms\Components\Repeater::make('criteria')->label('')
                    ->schema([
                        Forms\Components\Select::make('criterion')->label('Criterio')->options(ApplicationInterview::criteriaLabels())->required()->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        Forms\Components\TextInput::make('score')->label('Puntuación (0-10)')->numeric()->minValue(0)->maxValue(10)->required(),
                        Forms\Components\Textarea::make('comment')->label('Comentario')->required(),
                    ])->columns(3)->default(array_map(fn (string $criterion) => ['criterion' => $criterion, 'score' => 0, 'comment' => ''], array_keys(ApplicationInterview::criteriaLabels())))->minItems(count(ApplicationInterview::criteriaLabels()))->maxItems(count(ApplicationInterview::criteriaLabels()))->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('application.full_name')->label('Candidatura')->searchable(['application.first_name', 'application.last_name']),
            Tables\Columns\TextColumn::make('interviewed_at')->label('Fecha')->date('d/m/Y'),
            Tables\Columns\TextColumn::make('evaluator.name')->label('Evaluador/a'),
            Tables\Columns\TextColumn::make('status')->label('Estado')->badge(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplicationInterviews::route('/'),
            'create' => Pages\CreateApplicationInterview::route('/create'),
            'edit' => Pages\EditApplicationInterview::route('/{record}/edit'),
        ];
    }
}
