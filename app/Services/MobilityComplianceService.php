<?php

namespace App\Services;

use App\Models\DomainNotification;
use App\Models\Mobility;
use App\Models\MobilityDocument;
use App\Notifications\MobilityComplianceAlert;
use Carbon\CarbonInterface;

class MobilityComplianceService
{
    public function processDueChecks(CarbonInterface $today): array
    {
        $created = [];

        Mobility::with('documents')->each(function (Mobility $mobility) use ($today, &$created): void {
            if ($mobility->start_date?->isSameDay($today->copy()->addDays(7)) && $this->hasPendingPhaseDocuments($mobility, 'PRE')) {
                $created[] = $this->notify($mobility, 'pre_documents_due', $today, 'Quedan menos de siete días para la salida y hay documentos PRE pendientes.');
            }

            if ($mobility->end_date?->isSameDay($today->copy()->subDays(5)) && $this->hasMissingDocuments($mobility, ['confirmation_of_arrival', 'traineeship_certificate'])) {
                $created[] = $this->notify($mobility, 'post_documents_due', $today, 'Han transcurrido cinco días desde el fin de la movilidad y faltan documentos de estancia.');
            }

            if ($mobility->grant_agreement_signed_at?->isSameDay($today->copy()->subDays(20)) && ! $mobility->pre_financing_paid) {
                $created[] = $this->notify($mobility, 'pre_financing_due', $today, 'Han transcurrido veinte días desde el convenio sin registrar la prefinanciación.');
            }
        });

        return array_values(array_filter($created));
    }

    private function hasPendingPhaseDocuments(Mobility $mobility, string $phase): bool
    {
        return $mobility->documents->where('phase', $phase)->contains(
            fn (MobilityDocument $document) => ! in_array($document->validation_status, [MobilityDocument::STATUS_VALIDATED, MobilityDocument::STATUS_NOT_APPLICABLE], true),
        );
    }

    private function hasMissingDocuments(Mobility $mobility, array $types): bool
    {
        return $mobility->documents->whereIn('document_type', $types)->contains(
            fn (MobilityDocument $document) => ! in_array($document->validation_status, [MobilityDocument::STATUS_VALIDATED, MobilityDocument::STATUS_NOT_APPLICABLE], true),
        );
    }

    private function notify(Mobility $mobility, string $type, CarbonInterface $today, string $message): ?DomainNotification
    {
        $notification = DomainNotification::firstOrCreate(
            ['mobility_id' => $mobility->id, 'type' => $type, 'scheduled_for' => $today->toDateString()],
            ['message' => $message, 'status' => 'pending'],
        );

        if ($notification->wasRecentlyCreated && $mobility->user) {
            $mobility->user->notify(new MobilityComplianceAlert($notification));
        }

        return $notification;
    }
}
