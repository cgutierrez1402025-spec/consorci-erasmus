<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EducationalCenterResource\Pages;
use App\Models\EducationalCenter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EducationalCenterResource extends Resource
{
    protected static ?string $model = EducationalCenter::class;

    protected static ?string $navigationIcon = "heroicon-o-academic-cap";

    protected static ?string $navigationGroup = "Consorcio y Centros";

    protected static ?string $modelLabel = "Centro Educativo";

    protected static ?string $pluralModelLabel = "Centros del Consorcio";

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("Datos del Centro")
                    ->schema([
                        Forms\Components\TextInput::make("code")
                            ->label("Código de Centro")
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make("name")
                            ->label("Nombre del Centro")
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("city")
                            ->label("Localidad")
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make("province")
                            ->label("Provincia")
                            ->default("Valencia")
                            ->required(),
                        Forms\Components\Toggle::make("is_active")
                            ->label("Centro Activo en el Consorcio")
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make("Coordinación Erasmus del Centro")
                    ->schema([
                        Forms\Components\TextInput::make("coordinator_name")
                            ->label("Nombre del Coordinador/a")
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("coordinator_email")
                            ->label("Email del Coordinador/a")
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("coordinator_phone")
                            ->label("Teléfono de Contacto")
                            ->tel()
                            ->maxLength(30),
                        Forms\Components\Textarea::make("notes")
                            ->label("Observaciones")
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("code")
                    ->label("Código")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("name")
                    ->label("Centro")
                    ->sortable()
                    ->searchable()
                    ->weight("bold"),
                Tables\Columns\TextColumn::make("city")
                    ->label("Localidad")
                    ->sortable(),
                Tables\Columns\TextColumn::make("coordinator_name")
                    ->label("Coordinador/a")
                    ->searchable(),
                Tables\Columns\TextColumn::make("coordinator_email")
                    ->label("Email")
                    ->copyable(),
                Tables\Columns\IconColumn::make("is_active")
                    ->label("Activo")
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make("is_active")
                    ->label("Estado de adhesión"),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListEducationalCenters::route("/"),
            "create" => Pages\CreateEducationalCenter::route("/create"),
            "edit" => Pages\EditEducationalCenter::route("/{record}/edit"),
        ];
    }
}
