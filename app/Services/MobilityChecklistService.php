<?php

namespace App\Services;

use App\Models\Mobility;
use App\Models\MobilityDocument;
use App\Models\MobilityDocumentTemplate;

class MobilityChecklistService
{
    public function profileFor(Mobility $mobility): string
    {
        if ($mobility->participant_type === 'staff') {
            return 'staff';
        }

        return $mobility->call?->program_type === 'GS_ECHE' ? 'student_gs_eche' : 'student_gm_stepv';
    }

    public function synchronize(Mobility $mobility): void
    {
        MobilityDocumentTemplate::query()
            ->where('profile', $this->profileFor($mobility))
            ->get()
            ->each(fn (MobilityDocumentTemplate $template) => $mobility->documents()->firstOrCreate(
                ['document_type' => $template->document_type],
                [
                    'phase' => $template->phase,
                    'label' => $template->label,
                    'validation_status' => MobilityDocument::STATUS_PENDING_DELIVERY,
                ],
            ));
    }

    public function completionPercentage(Mobility $mobility): int
    {
        $documents = $mobility->documents()->where('validation_status', '!=', MobilityDocument::STATUS_NOT_APPLICABLE)->get();
        if ($documents->isEmpty()) {
            return 0;
        }

        return (int) round($documents->where('validation_status', MobilityDocument::STATUS_VALIDATED)->count() / $documents->count() * 100);
    }
}
