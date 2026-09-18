<?php

namespace App\Filament\Resources;

use App\Enums\CountryGroup;
use App\Filament\Resources\HostPartnerResource\Pages;
use App\Models\HostPartner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HostPartnerResource extends Resource
{
    protected static ?string $model = HostPartner::class;

    protected static ?string $navigationIcon = "heroicon-o-building-office-2";

    protected static ?string $navigationGroup = "Socios y Empresas";

    protected static ?string $modelLabel = "Empresa / Socio de Acogida";

    protected static ?string $pluralModelLabel = "Empresas y Socios de Acogida";

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("Datos de la Entidad Europea")
                    ->schema([
                        Forms\Components\TextInput::make("name")
                            ->label("Nombre de la Empresa o Institución")
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("vat_number")
                            ->label("NIF / VAT Number Europeo")
                            ->maxLength(50),
                        Forms\Components\TextInput::make("country_code")
                            ->label("Código País (2 letras: DE, IT, IE, FR, PT...)")
                            ->required()
                            ->maxLength(5)
                            ->default("IT"),
                        Forms\Components\Select::make("country_group")
                            ->label("Grupo de País SEPIE")
                            ->options([
                                1 => "Grupo 1 (Vida alta: DE, IT, FR, IE, SE...)",
                                2 => "Grupo 2 (Vida media: PT, EL, CZ, SI...)",
                                3 => "Grupo 3 (Vida baja: PL, RO, BG, HR...)",
                            ])
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make("city")
                            ->label("Ciudad de Destino")
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make("sector")
                            ->label("Sector Profesional / Familia FP")
                            ->placeholder("Informática y Comunicaciones, Electricidad, Sanidad...")
                            ->maxLength(150),
                        Forms\Components\TextInput::make("address")
                            ->label("Dirección Completa")
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make("is_active")
                            ->label("Colaborador Activo")
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make("Tutor / Persona de Contacto en Destino")
                    ->schema([
                        Forms\Components\TextInput::make("contact_person")
                            ->label("Tutor/a de Empresa")
                            ->maxLength(255),
                        Forms\Components\TextInput::make("contact_email")
                            ->label("Email Tutor/a")
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("contact_phone")
                            ->label("Teléfono")
                            ->tel()
                            ->maxLength(50),
                        Forms\Components\TextInput::make("website")
                            ->label("Sitio Web")
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TagsInput::make("working_languages")
                            ->label("Idiomas de Trabajo")
                            ->default(["Inglés"]),
                        Forms\Components\Textarea::make("notes")
                            ->label("Observaciones / Perfil de alumnos acogidos")
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")
                    ->label("Empresa / Institución")
                    ->weight("bold")
                    ->searchable(),
                Tables\Columns\TextColumn::make("country_code")
                    ->label("País")
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make("city")
                    ->label("Ciudad")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("sector")
                    ->label("Sector")
                    ->limit(25)
                    ->searchable(),
                Tables\Columns\TextColumn::make("contact_person")
                    ->label("Tutor/a")
                    ->searchable(),
                Tables\Columns\TextColumn::make("contact_email")
                    ->label("Email")
                    ->copyable(),
                Tables\Columns\IconColumn::make("is_active")
                    ->label("Activo")
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("country_code")
                    ->label("Filtrar por País"),
                Tables\Filters\TernaryFilter::make("is_active")
                    ->label("Activos"),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListHostPartners::route("/"),
            "create" => Pages\CreateHostPartner::route("/create"),
            "edit" => Pages\EditHostPartner::route("/{record}/edit"),
        ];
    }
}
