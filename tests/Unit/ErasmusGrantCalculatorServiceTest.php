<?php

namespace Tests\Unit;

use App\Services\ErasmusGrantCalculatorService;
use Carbon\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class ErasmusGrantCalculatorServiceTest extends TestCase
{
    public function test_calculates_long_term_duration_under_the_eu_thirty_day_convention(): void
    {
        $result = (new ErasmusGrantCalculatorService)->calculateLongTermDays(
            Carbon::parse('2026-01-31'),
            Carbon::parse('2026-02-28'),
        );

        $this->assertSame(['total_days' => 31, 'full_months' => 1, 'extra_days' => 1], $result);
    }

    public function test_calculates_long_term_student_grant_with_all_top_ups(): void
    {
        $result = (new ErasmusGrantCalculatorService)->calculateLongTermStudentGrant(
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-01-30'),
            500,
            true,
            true,
            275,
        );

        $this->assertSame(900.0, $result['total_monthly_amount']);
        $this->assertSame(900.0, $result['individual_support']);
        $this->assertSame(1175.0, $result['total_grant']);
    }

    public function test_calculates_short_term_student_grant(): void
    {
        $result = (new ErasmusGrantCalculatorService)->calculateShortTermStudentGrant(
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-01-16'),
            true,
            100,
        );

        $this->assertSame(16, $result['total_days']);
        $this->assertSame(980, $result['amount_1_14']);
        $this->assertSame(100, $result['amount_15_36']);
        $this->assertSame(150.0, $result['fewer_opp_topup']);
        $this->assertSame(1330.0, $result['total_grant']);
    }

    public function test_calculates_staff_grant_with_reduced_rate_after_fourteen_days(): void
    {
        $result = (new ErasmusGrantCalculatorService)->calculateStaffGrant(
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-01-16'),
            100,
            275,
        );

        $this->assertSame(70.0, $result['daily_rate_15_66']);
        $this->assertSame(1540.0, $result['individual_support']);
        $this->assertSame(1815.0, $result['total_grant']);
    }

    public function test_rejects_invalid_long_term_dates(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ErasmusGrantCalculatorService)->calculateLongTermDays(
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-01-01'),
        );
    }
}
