<?php

namespace App\Filament\Pages;

use App\Services\ErasmusGrantCalculatorService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;

class FinancialCalculator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Convocatorias y Baremos';

    protected static ?string $navigationLabel = 'Calculadora financiera';

    protected static ?string $title = 'Calculadora de ayudas Erasmus+';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.financial-calculator';

    public ?array $data = [];

    public ?array $result = null;

    public function mount(): void
    {
        $this->form->fill([
            'grant_type' => 'long_term_student',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(29)->toDateString(),
            'monthly_base_amount' => 700,
            'daily_country_rate' => 100,
            'travel_support_amount' => 275,
            'has_fewer_opportunities' => false,
            'is_traineeship' => false,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de movilidad')
                    ->description('Estimación interna. Confirme importes y normativa de la convocatoria antes de formalizar la ayuda.')
                    ->schema([
                        Forms\Components\Select::make('grant_type')
                            ->label('Tipo de movilidad')
                            ->options([
                                'long_term_student' => 'Alumnado de larga duración',
                                'short_term_student' => 'Alumnado de corta duración / BIP',
                                'staff' => 'Profesorado / personal',
                            ])
                            ->live()
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')->label('Fecha de inicio')->required(),
                        Forms\Components\DatePicker::make('end_date')->label('Fecha de fin')->required()->after('start_date'),
                        Forms\Components\TextInput::make('monthly_base_amount')
                            ->label('Importe mensual base')
                            ->numeric()
                            ->prefix('€')
                            ->visible(fn (Forms\Get $get): bool => $get('grant_type') === 'long_term_student')
                            ->required(fn (Forms\Get $get): bool => $get('grant_type') === 'long_term_student'),
                        Forms\Components\TextInput::make('daily_country_rate')
                            ->label('Tarifa diaria de país')
                            ->numeric()
                            ->prefix('€')
                            ->visible(fn (Forms\Get $get): bool => $get('grant_type') === 'staff')
                            ->required(fn (Forms\Get $get): bool => $get('grant_type') === 'staff'),
                        Forms\Components\TextInput::make('travel_support_amount')->label('Ayuda de viaje')->numeric()->prefix('€')->minValue(0)->required(),
                        Forms\Components\Toggle::make('is_traineeship')
                            ->label('Movilidad de prácticas (+150 €/mes)')
                            ->visible(fn (Forms\Get $get): bool => $get('grant_type') === 'long_term_student'),
                        Forms\Components\Toggle::make('has_fewer_opportunities')->label('Menos oportunidades'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function calculate(): void
    {
        $data = $this->form->getState();
        $calculator = new ErasmusGrantCalculatorService;
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        $this->result = match ($data['grant_type']) {
            'long_term_student' => $calculator->calculateLongTermStudentGrant($start, $end, (float) $data['monthly_base_amount'], (bool) $data['is_traineeship'], (bool) $data['has_fewer_opportunities'], (float) $data['travel_support_amount']),
            'short_term_student' => $calculator->calculateShortTermStudentGrant($start, $end, (bool) $data['has_fewer_opportunities'], (float) $data['travel_support_amount']),
            default => $calculator->calculateStaffGrant($start, $end, (float) $data['daily_country_rate'], (float) $data['travel_support_amount']),
        };
    }

    #[Computed]
    public function formattedResult(): array
    {
        if (! $this->result) {
            return [];
        }

        return collect($this->result)
            ->except('duration')
            ->filter(fn ($value): bool => is_numeric($value))
            ->mapWithKeys(fn ($value, $key): array => [str_replace('_', ' ', ucfirst($key)) => number_format((float) $value, 2, ',', '.').' €'])
            ->all();
    }
}
