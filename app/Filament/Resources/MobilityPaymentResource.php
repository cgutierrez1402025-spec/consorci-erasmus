<?php

namespace App\Filament\Resources;

use App\Enums\PaymentStatus;
use App\Filament\Resources\MobilityPaymentResource\Pages;
use App\Models\MobilityPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MobilityPaymentResource extends Resource
{
    protected static ?string $model = MobilityPayment::class;

    protected static ?string $navigationIcon = "heroicon-o-banknotes";

    protected static ?string $navigationGroup = "Movilidades y Estancias";

    protected static ?string $modelLabel = "Pago de Subvención";

    protected static ?string $pluralModelLabel = "Pagos y Liquidaciones";

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("Detalles del Pago")
                    ->schema([
                        Forms\Components\Select::make("mobility_id")
                            ->label("Movilidad / Participante")
                            ->relationship("mobility", "participant_name")
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make("payment_type")
                            ->label("Tipo de Pago")
                            ->options([
                                "first_payment_80" => "Anticipo 80% (Firma Convenio)",
                                "final_balance_20" => "Liquidación Final 20% (Fin Estancia)",
                                "travel_reimbursement" => "Reembolso Gastos de Viaje Extraordinario",
                                "other" => "Otro concepto",
                            ])
                            ->default("first_payment_80")
                            ->required(),
                        Forms\Components\TextInput::make("amount")
                            ->label("Importe (€)")
                            ->numeric()
                            ->prefix("€")
                            ->required(),
                        Forms\Components\DatePicker::make("scheduled_date")
                            ->label("Fecha Programada")
                            ->required(),
                        Forms\Components\DatePicker::make("paid_date")
                            ->label("Fecha de Abono Efectivo"),
                        Forms\Components\Select::make("status")
                            ->label("Estado del Pago")
                            ->options([
                                PaymentStatus::Pending->value => PaymentStatus::Pending->getLabel(),
                                PaymentStatus::Approved->value => PaymentStatus::Approved->getLabel(),
                                PaymentStatus::Paid->value => PaymentStatus::Paid->getLabel(),
                                PaymentStatus::Cancelled->value => PaymentStatus::Cancelled->getLabel(),
                            ])
                            ->default(PaymentStatus::Pending->value)
                            ->required(),
                        Forms\Components\TextInput::make("reference_number")
                            ->label("Referencia Bancaria / Justificante Contable")
                            ->maxLength(100),
                        Forms\Components\Textarea::make("notes")
                            ->label("Notas")
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("mobility.participant_name")
                    ->label("Participante")
                    ->weight("bold")
                    ->searchable(),
                Tables\Columns\TextColumn::make("payment_type")
                    ->label("Concepto")
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        "first_payment_80" => "Anticipo 80%",
                        "final_balance_20" => "Liquidación 20%",
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make("amount")
                    ->label("Importe")
                    ->money("EUR")
                    ->sortable()
                    ->weight("bold"),
                Tables\Columns\TextColumn::make("scheduled_date")
                    ->label("Previsto")
                    ->date("d/m/Y"),
                Tables\Columns\TextColumn::make("paid_date")
                    ->label("Abonado")
                    ->date("d/m/Y")
                    ->placeholder("Pendiente"),
                Tables\Columns\BadgeColumn::make("status")
                    ->label("Estado")
                    ->formatStateUsing(fn ($state) => $state instanceof PaymentStatus ? $state->getLabel() : $state)
                    ->colors([
                        "warning" => PaymentStatus::Pending->value,
                        "info" => PaymentStatus::Approved->value,
                        "success" => PaymentStatus::Paid->value,
                        "danger" => PaymentStatus::Cancelled->value,
                    ]),
                Tables\Columns\TextColumn::make("reference_number")
                    ->label("Ref. Contable")
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("status")
                    ->label("Estado"),
            ])
            ->actions([
                Tables\Actions\Action::make("markAsPaid")
                    ->label("Marcar Abonado")
                    ->icon("heroicon-o-check-circle")
                    ->color("success")
                    ->visible(fn (MobilityPayment $record) => $record->status !== PaymentStatus::Paid)
                    ->action(function (MobilityPayment $record) {
                        $record->update([
                            "status" => PaymentStatus::Paid,
                            "paid_date" => now(),
                        ]);
                        Notification::make()
                            ->title("Pago Confirmado")
                            ->body("El pago de {$record->amount}€ ha sido marcado como abonado.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListMobilityPayments::route("/"),
            "create" => Pages\CreateMobilityPayment::route("/create"),
            "edit" => Pages\EditMobilityPayment::route("/{record}/edit"),
        ];
    }
}
