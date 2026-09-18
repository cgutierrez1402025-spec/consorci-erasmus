<?php

namespace App\Services;

use App\Enums\CountryGroup;

class GrantCalculationService
{
    /**
     * Country group classification according to SEPIE Erasmus+ rules.
     */
    public const COUNTRY_GROUPS = [
        // Group 1: High living costs
        "DK" => 1, "IE" => 1, "FR" => 1, "IT" => 1, "AT" => 1, "FI" => 1, "SE" => 1, "NO" => 1, "IS" => 1, "LI" => 1,
        // Group 2: Medium living costs
        "BE" => 2, "DE" => 2, "EL" => 2, "GR" => 2, "ES" => 2, "CY" => 2, "NL" => 2, "MT" => 2, "PT" => 2, "CZ" => 2, "SI" => 2,
        // Group 3: Lower living costs
        "BG" => 3, "HR" => 3, "EE" => 3, "LV" => 3, "LT" => 3, "HU" => 3, "PL" => 3, "RO" => 3, "SK" => 3, "MK" => 3, "RS" => 3, "TR" => 3,
    ];

    /**
     * Daily individual support rate by country group for VET students.
     */
    public const DAILY_RATES = [
        1 => 54.00,
        2 => 47.00,
        3 => 41.00,
    ];

    /**
     * Calculate comprehensive grant breakdown.
     */
    public function calculate(
        string $countryCode,
        int $durationDays,
        string $travelType = "standard",
        bool $fewerOpportunities = false,
        string $participantType = "student"
    ): array {
        $countryCode = strtoupper($countryCode);
        $group = self::COUNTRY_GROUPS[$countryCode] ?? 2;
        $dailyRate = self::DAILY_RATES[$group];

        // Staff has higher daily allowance for shorter stays
        if ($participantType === "staff") {
            $dailyRate = match ($group) {
                1 => 135.00,
                2 => 120.00,
                3 => 105.00,
            };
        }

        // Individual support
        $individualSupport = round($dailyRate * $durationDays, 2);

        // Travel support (standard default 275 EUR, Green Travel 360 EUR)
        $travelAmount = ($travelType === "green_travel") ? 360.00 : 275.00;

        // Inclusion support (+250€ for up to 30 days, or +8.33€/day for longer)
        $inclusionAmount = 0.00;
        if ($fewerOpportunities) {
            $months = ceil($durationDays / 30);
            $inclusionAmount = round($months * 250.00, 2);
        }

        $totalGrant = round($individualSupport + $travelAmount + $inclusionAmount, 2);

        // Standard SEPIE payment schedule: 80% advance upon signing, 20% final balance after EU Survey
        $firstPayment80 = round($totalGrant * 0.80, 2);
        $finalPayment20 = round($totalGrant - $firstPayment80, 2);

        return [
            "country_group" => $group,
            "daily_rate" => $dailyRate,
            "duration_days" => $durationDays,
            "individual_support" => $individualSupport,
            "travel_amount" => $travelAmount,
            "inclusion_amount" => $inclusionAmount,
            "total_grant" => $totalGrant,
            "first_payment_80" => $firstPayment80,
            "final_balance_20" => $finalPayment20,
        ];
    }
}
