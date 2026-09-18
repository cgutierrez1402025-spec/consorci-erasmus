<?php

namespace Tests\Unit;

use App\Services\GrantCalculationService;
use PHPUnit\Framework\TestCase;

class GrantCalculationServiceTest extends TestCase
{
    public function test_it_calculates_standard_student_grant_for_group_1_country(): void
    {
        $service = new GrantCalculationService();
        // Italy is Group 1: 54 EUR/day, 30 days = 1620 EUR + 275 EUR standard travel = 1895 EUR
        $result = $service->calculate("IT", 30, "standard", false, "student");

        $this->assertEquals(1, $result["country_group"]);
        $this->assertEquals(54.00, $result["daily_rate"]);
        $this->assertEquals(1620.00, $result["individual_support"]);
        $this->assertEquals(275.00, $result["travel_amount"]);
        $this->assertEquals(0.00, $result["inclusion_amount"]);
        $this->assertEquals(1895.00, $result["total_grant"]);
        $this->assertEquals(1516.00, $result["first_payment_80"]);
        $this->assertEquals(379.00, $result["final_balance_20"]);
    }

    public function test_it_includes_green_travel_and_inclusion_bonus(): void
    {
        $service = new GrantCalculationService();
        // Germany is Group 2: 47 EUR/day * 30 days = 1410 EUR + 360 EUR green travel + 250 EUR inclusion = 2020 EUR
        $result = $service->calculate("DE", 30, "green_travel", true, "student");

        $this->assertEquals(2, $result["country_group"]);
        $this->assertEquals(47.00, $result["daily_rate"]);
        $this->assertEquals(1410.00, $result["individual_support"]);
        $this->assertEquals(360.00, $result["travel_amount"]);
        $this->assertEquals(250.00, $result["inclusion_amount"]);
        $this->assertEquals(2020.00, $result["total_grant"]);
        $this->assertEquals(1616.00, $result["first_payment_80"]);
        $this->assertEquals(404.00, $result["final_balance_20"]);
    }

    public function test_it_calculates_staff_mobility_higher_daily_rate(): void
    {
        $service = new GrantCalculationService();
        // France is Group 1: Staff daily rate is 135 EUR/day, 5 days = 675 EUR + 275 EUR travel = 950 EUR
        $result = $service->calculate("FR", 5, "standard", false, "staff");

        $this->assertEquals(135.00, $result["daily_rate"]);
        $this->assertEquals(675.00, $result["individual_support"]);
        $this->assertEquals(950.00, $result["total_grant"]);
    }
}
