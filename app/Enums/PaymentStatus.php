<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = "pending";
    case Approved = "approved";
    case Paid = "paid";
    case Cancelled = "cancelled";

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => "Pendiente",
            self::Approved => "Aprobado para pago",
            self::Paid => "Abonado",
            self::Cancelled => "Cancelado",
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => "warning",
            self::Approved => "info",
            self::Paid => "success",
            self::Cancelled => "danger",
        };
    }
}
