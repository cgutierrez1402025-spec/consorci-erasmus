<?php

namespace App\Enums;

enum MobilityType: string
{
    case VetStudentShort = "vet_student_short";
    case VetStudentLong = "vet_student_long";
    case VetBasic = "vet_basic";
    case HigherVet = "higher_vet";
    case StaffTraining = "staff_training";

    public function getLabel(): string
    {
        return match ($this) {
            self::VetStudentShort => "FP Grado Medio - Corta duración (2 a 4 semanas)",
            self::VetStudentLong => "FP Grado Medio - ErasmusPro (3 a 6 meses)",
            self::VetBasic => "FP Básica (2 semanas)",
            self::HigherVet => "FP Grado Superior (FCT / Recién titulados)",
            self::StaffTraining => "Personal Docente (Job Shadowing / Formación)",
        };
    }
}
