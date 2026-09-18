<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\MobilityStatus;
use App\Models\Application;
use App\Models\EducationalCenter;
use App\Models\Mobility;
use App\Models\MobilityPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ConsortiumStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $centersCount = EducationalCenter::where("is_active", true)->count();
        $totalMobilities = Mobility::count();
        $activeMobilities = Mobility::where("status", MobilityStatus::InProgress)->count();
        
        $totalGrant = Mobility::sum("total_grant_amount");
        $paidGrant = MobilityPayment::where("status", "paid")->sum("amount");
        
        $totalApplications = Application::count();
        $admittedApplications = Application::where("status", ApplicationStatus::Admitted)->count();

        return [
            Stat::make("Centros Asociados", $centersCount)
                ->description("Institutos activos en consorcio")
                ->descriptionIcon("heroicon-m-academic-cap")
                ->color("primary"),

            Stat::make("Movilidades Gestionadas", $totalMobilities)
                ->description("{$activeMobilities} actualmente en estancia europea")
                ->descriptionIcon("heroicon-m-globe-europe-africa")
                ->color("success"),

            Stat::make("Subvención SEPIE / UE", number_format($totalGrant, 2, ",", ".") . " €")
                ->description(number_format($paidGrant, 2, ",", ".") . " € ya abonados a becarios")
                ->descriptionIcon("heroicon-m-banknotes")
                ->color("warning"),

            Stat::make("Candidaturas Recibidas", $totalApplications)
                ->description("{$admittedApplications} admitidas / plazas cubiertas")
                ->descriptionIcon("heroicon-m-clipboard-document-check")
                ->color("info"),
        ];
    }
}
