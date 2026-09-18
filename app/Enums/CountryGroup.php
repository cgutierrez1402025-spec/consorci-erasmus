<?php

namespace App\Enums;

enum CountryGroup: int
{
    case Group1 = 1;
    case Group2 = 2;
    case Group3 = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Group1 => "Grupo 1 (Coste de vida alto: Dinamarca, Irlanda, Francia, Italia, Austria, Finlandia, Suecia, Noruega)",
            self::Group2 => "Grupo 2 (Coste de vida medio: Alemania, Bélgica, Chipre, Grecia, España, Países Bajos, Portugal, Chequia)",
            self::Group3 => "Grupo 3 (Coste de vida bajo: Bulgaria, Croacia, Estonia, Letonia, Lituania, Hungría, Polonia, Rumanía, Eslovaquia)",
        };
    }

    public function getDefaultDailyRate(): float
    {
        return match ($this) {
            self::Group1 => 54.00,
            self::Group2 => 47.00,
            self::Group3 => 41.00,
        };
    }
}
