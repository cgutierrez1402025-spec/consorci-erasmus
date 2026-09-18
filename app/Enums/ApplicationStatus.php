<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Submitted = "submitted";
    case InReview = "in_review";
    case Scored = "scored";
    case Admitted = "admitted";
    case Reserve = "reserve";
    case Rejected = "rejected";
    case Withdrawn = "withdrawn";

    public function getLabel(): string
    {
        return match ($this) {
            self::Submitted => "Presentada",
            self::InReview => "En revisión",
            self::Scored => "Baremada",
            self::Admitted => "Admitida / Seleccionada",
            self::Reserve => "Lista de espera / Suplente",
            self::Rejected => "Desestimada / Excluida",
            self::Withdrawn => "Renuncia voluntaria",
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Submitted => "gray",
            self::InReview => "warning",
            self::Scored => "info",
            self::Admitted => "success",
            self::Reserve => "warning",
            self::Rejected => "danger",
            self::Withdrawn => "secondary",
        };
    }
}
