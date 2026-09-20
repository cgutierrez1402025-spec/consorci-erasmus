<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class ErasmusGrantCalculatorService
{
    public function calculateLongTermDays(Carbon $startDate, Carbon $endDate): array
    {
        if ($startDate->gte($endDate)) {
            throw new InvalidArgumentException('La fecha de inicio debe ser anterior a la fecha de fin.');
        }

        $startDay = $startDate->day === 31 ? 30 : $startDate->day;
        $isEndOfFebruary = $endDate->month === 2 && in_array($endDate->day, [28, 29], true);
        $endDay = ($endDate->day === 31 || $isEndOfFebruary) ? 30 : $endDate->day;
        $totalDays = (($endDate->year - $startDate->year) * 360)
            + (($endDate->month - $startDate->month) * 30)
            + ($endDay - $startDay) + 1;
        $fullMonths = (int) floor($totalDays / 30);

        return [
            'total_days' => $totalDays,
            'full_months' => $fullMonths,
            'extra_days' => $totalDays - ($fullMonths * 30),
        ];
    }

    public function calculateLongTermStudentGrant(
        Carbon $startDate,
        Carbon $endDate,
        float $monthlyBaseAmount,
        bool $isTraineeship = false,
        bool $hasFewerOpportunities = false,
        float $travelSupportAmount = 0.0,
    ): array {
        $duration = $this->calculateLongTermDays($startDate, $endDate);
        $traineeshipTopUp = $isTraineeship ? 150.0 : 0.0;
        $fewerOpportunitiesTopUp = $hasFewerOpportunities ? 250.0 : 0.0;
        $monthlyAmount = $monthlyBaseAmount + $traineeshipTopUp + $fewerOpportunitiesTopUp;
        $dailyRate = $monthlyAmount / 30.0;
        $individualSupport = round(($duration['full_months'] * $monthlyAmount) + ($duration['extra_days'] * $dailyRate), 0);

        return [
            'duration' => $duration,
            'monthly_base' => $monthlyBaseAmount,
            'traineeship_topup' => $traineeshipTopUp,
            'fewer_opp_topup' => $fewerOpportunitiesTopUp,
            'total_monthly_amount' => $monthlyAmount,
            'daily_rate' => $dailyRate,
            'individual_support' => $individualSupport,
            'travel_support' => $travelSupportAmount,
            'total_grant' => $individualSupport + $travelSupportAmount,
        ];
    }

    public function calculateShortTermStudentGrant(
        Carbon $startDate,
        Carbon $endDate,
        bool $hasFewerOpportunities = false,
        float $travelSupportAmount = 0.0,
    ): array {
        $this->ensureSameOrLaterEndDate($startDate, $endDate);
        $totalDays = (int) $startDate->diffInDays($endDate) + 1;
        $days1To14 = min($totalDays, 14);
        $days15To36 = max(0, $totalDays - 14);
        $amount1To14 = $days1To14 * 70;
        $amount15To36 = $days15To36 * 50;
        $fewerOpportunitiesTopUp = $hasFewerOpportunities ? ($totalDays > 14 ? 150.0 : 100.0) : 0.0;
        $individualSupport = $amount1To14 + $amount15To36 + $fewerOpportunitiesTopUp;

        return [
            'total_days' => $totalDays,
            'days_1_14' => $days1To14,
            'days_15_36' => $days15To36,
            'amount_1_14' => $amount1To14,
            'amount_15_36' => $amount15To36,
            'fewer_opp_topup' => $fewerOpportunitiesTopUp,
            'individual_support' => $individualSupport,
            'travel_support' => $travelSupportAmount,
            'total_grant' => $individualSupport + $travelSupportAmount,
        ];
    }

    public function calculateStaffGrant(
        Carbon $startDate,
        Carbon $endDate,
        float $dailyCountryRate,
        float $travelSupportAmount = 0.0,
    ): array {
        $this->ensureSameOrLaterEndDate($startDate, $endDate);
        $totalDays = (int) $startDate->diffInDays($endDate) + 1;
        $days1To14 = min($totalDays, 14);
        $days15To66 = max(0, $totalDays - 14);
        $reducedDailyRate = $dailyCountryRate * 0.70;
        $amount1To14 = $days1To14 * $dailyCountryRate;
        $amount15To66 = $days15To66 * $reducedDailyRate;
        $individualSupport = round($amount1To14 + $amount15To66, 0);

        return [
            'total_days' => $totalDays,
            'days_1_14' => $days1To14,
            'days_15_66' => $days15To66,
            'daily_rate_1_14' => $dailyCountryRate,
            'daily_rate_15_66' => $reducedDailyRate,
            'amount_1_14' => $amount1To14,
            'amount_15_66' => $amount15To66,
            'individual_support' => $individualSupport,
            'travel_support' => $travelSupportAmount,
            'total_grant' => $individualSupport + $travelSupportAmount,
        ];
    }

    private function ensureSameOrLaterEndDate(Carbon $startDate, Carbon $endDate): void
    {
        if ($startDate->gt($endDate)) {
            throw new InvalidArgumentException('La fecha de inicio no puede ser posterior a la fecha de fin.');
        }
    }
}
