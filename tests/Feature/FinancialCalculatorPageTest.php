<?php

namespace Tests\Feature;

use App\Filament\Pages\FinancialCalculator;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinancialCalculatorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_and_calculate_from_the_financial_calculator_module(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($user)
            ->test(FinancialCalculator::class)
            ->set('data.grant_type', 'short_term_student')
            ->set('data.start_date', '2026-01-01')
            ->set('data.end_date', '2026-01-14')
            ->set('data.travel_support_amount', 275)
            ->call('calculate')
            ->assertSet('result.total_grant', 1255.0);
    }
}
