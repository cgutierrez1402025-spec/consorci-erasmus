<?php

namespace App\Enums;

enum MobilityStatus: string
{
    case Planned = "planned";
    case Contracted = "contracted";
    case InProgress = "in_progress";
    case Completed = "completed";
    case Cancelled = "cancelled";

    public function getLabel(): string
    {
        return match ($this) {
            self::Planned => "Planificada",
            self::Contracted => "Convenio firmado",
            self::InProgress => "En estancia / Activa",
            self::Completed => "Finalizada / Justificada",
            self::Cancelled => "Cancelada",
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Planned => "gray",
            self::Contracted => "info",
            self::InProgress => "warning",
            self::Completed => "success",
            self::Cancelled => "danger",
        };
    }
}
