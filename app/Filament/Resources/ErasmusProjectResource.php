<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ErasmusProjectResource\Pages;
use App\Models\ErasmusProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ErasmusProjectResource extends Resource
{
    protected static ?string $model = ErasmusProject::class;

    protected static ?string $navigationIcon = "heroicon-o-folder-open";

    protected static ?string $navigationGroup = "Consorcio y Centros";

    protected static ?string $modelLabel = "Proyecto SEPIE";

    protected static ?string $pluralModelLabel = "Proyectos Erasmus+";

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("Identificación del Proyecto")
                    ->schema([
                        Forms\Components\TextInput::make("project_code")
                            ->label("Código Proyecto SEPIE")
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder("2024-1-ES01-KA121-VET-00012345"),
                        Forms\Components\TextInput::make("title")
                            ->label("Título del Proyecto")
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("call_year")
                            ->label("Año Convocatoria")
                            ->default(date("Y"))
                            ->required(),
                        Forms\Components\TextInput::make("academic_year")
                            ->label("Curso Académico")
                            ->default("2024-2025")
                            ->required(),
                        Forms\Components\DatePicker::make("start_date")
                            ->label("Fecha Inicio")
                            ->required(),
                        Forms\Components\DatePicker::make("end_date")
                            ->label("Fecha Fin")
                            ->required(),
                        Forms\Components\Select::make("status")
                            ->label("Estado")
                            ->options([
                                "draft" => "Borrador / Preparación",
                                "active" => "Activo / En ejecución",
                                "reporting" => "En justificación final",
                                "closed" => "Cerrado",
                            ])
                            ->default("active")
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make("Desglose Económico Concedido (SEPIE / UE)")
                    ->schema([
                        Forms\Components\TextInput::make("total_grant_awarded")
                            ->label("Subvención Total (€)")
                            ->numeric()
                            ->prefix("€")
                            ->required(),
                        Forms\Components\TextInput::make("individual_support_grant")
                            ->label("Apoyo Individual (€)")
                            ->numeric()
                            ->prefix("€")
                            ->default(0),
                        Forms\Components\TextInput::make("travel_grant")
                            ->label("Ayuda de Viaje (€)")
                            ->numeric()
                            ->prefix("€")
                            ->default(0),
                        Forms\Components\TextInput::make("organizational_support_grant")
                            ->label("Apoyo Organizativo OS (€)")
                            ->numeric()
                            ->prefix("€")
                            ->default(0),
                        Forms\Components\TextInput::make("inclusion_support_grant")
                            ->label("Apoyo para Inclusión (€)")
                            ->numeric()
                            ->prefix("€")
                            ->default(0),
                        Forms\Components\Textarea::make("notes")
                            ->label("Observaciones / Condiciones especiales")
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("project_code")
                    ->label("Código Proyecto")
                    ->sortable()
                    ->searchable()
                    ->weight("bold"),
                Tables\Columns\TextColumn::make("title")
                    ->label("Título")
                    ->limit(35)
                    ->searchable(),
                Tables\Columns\TextColumn::make("academic_year")
                    ->label("Curso")
                    ->sortable(),
                Tables\Columns\TextColumn::make("total_grant_awarded")
                    ->label("Dotación Total")
                    ->money("EUR")
                    ->sortable(),
                Tables\Columns\TextColumn::make("start_date")
                    ->label("Inicio")
                    ->date("d/m/Y"),
                Tables\Columns\TextColumn::make("end_date")
                    ->label("Fin")
                    ->date("d/m/Y"),
                Tables\Columns\BadgeColumn::make("status")
                    ->label("Estado")
                    ->colors([
                        "success" => "active",
                        "warning" => "reporting",
                        "gray" => "draft",
                        "secondary" => "closed",
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListErasmusProjects::route("/"),
            "create" => Pages\CreateErasmusProject::route("/create"),
            "edit" => Pages\EditErasmusProject::route("/{record}/edit"),
        ];
    }
}
