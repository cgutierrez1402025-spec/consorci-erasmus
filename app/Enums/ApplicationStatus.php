<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case DocumentationPending = 'documentation_pending';
    case DocumentationComplete = 'documentation_complete';
    case InterviewPending = 'interview_pending';
    case InterviewCompleted = 'interview_completed';
    case InReview = 'in_review';
    case Scored = 'scored';
    case Ineligible = 'ineligible';
    case Admitted = 'admitted';
    case Reserve = 'reserve';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    public function getLabel(): string
    {
        return match ($this) {
            self::Submitted => 'Presentada',
            self::DocumentationPending => 'Documentación pendiente',
            self::DocumentationComplete => 'Documentación completa',
            self::InterviewPending => 'Entrevista pendiente',
            self::InterviewCompleted => 'Entrevista realizada',
            self::InReview => 'En revisión',
            self::Scored => 'Baremada',
            self::Ineligible => 'No elegible',
            self::Admitted => 'Admitida / Seleccionada',
            self::Reserve => 'Lista de espera / Suplente',
            self::Rejected => 'Desestimada / Excluida',
            self::Withdrawn => 'Renuncia voluntaria',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Submitted => 'gray',
            self::DocumentationPending => 'warning',
            self::DocumentationComplete => 'info',
            self::InterviewPending => 'warning',
            self::InterviewCompleted => 'info',
            self::InReview => 'warning',
            self::Scored => 'info',
            self::Ineligible => 'danger',
            self::Admitted => 'success',
            self::Reserve => 'warning',
            self::Rejected => 'danger',
            self::Withdrawn => 'secondary',
        };
    }
}
