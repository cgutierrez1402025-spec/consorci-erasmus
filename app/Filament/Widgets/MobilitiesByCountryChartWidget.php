<?php

namespace App\Filament\Widgets;

use App\Models\Mobility;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MobilitiesByCountryChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = "Distribución de Movilidades por País de Destino";

    protected function getData(): array
    {
        $data = Mobility::query()
            ->select("destination_country", DB::raw("count(*) as total"))
            ->groupBy("destination_country")
            ->orderByDesc("total")
            ->pluck("total", "destination_country")
            ->toArray();

        $labels = array_keys($data);
        $values = array_values($data);

        if (empty($labels)) {
            $labels = ["IT (Italia)", "DE (Alemania)", "IE (Irlanda)", "FR (Francia)", "PT (Portugal)"];
            $values = [8, 6, 4, 3, 2];
        }

        return [
            "datasets" => [
                [
                    "label" => "Nº de participantes",
                    "data" => $values,
                    "backgroundColor" => [
                        "#003399", "#2563eb", "#38bdf8", "#0284c7", "#f59e0b", "#10b981", "#8b5cf6"
                    ],
                ],
            ],
            "labels" => $labels,
        ];
    }

    protected function getType(): string
    {
        return "bar";
    }
}
