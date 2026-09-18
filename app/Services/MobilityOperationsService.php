<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\MobilityStatus;
use App\Models\DomainNotification;
use App\Models\Mobility;
use App\Models\MobilityBond;
use App\Models\MobilityWithdrawal;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class MobilityOperationsService
{
    public function validateSelectedParticipantBanking(Mobility $mobility): void
    {
        $iban = strtoupper(str_replace([' ', '-'], '', (string) $mobility->iban));
        if (! preg_match('/^ES\d{22}$/', $iban) || $mobility->bank_account_holder !== $mobility->participant_name) {
            throw ValidationException::withMessages(['iban' => 'El IBAN debe ser español (ES + 22 dígitos) y el titular debe coincidir exactamente con el participante.']);
        }
    }

    public function bondIsValid(Mobility $mobility): bool
    {
        $bond = $mobility->bond;

        return $bond !== null && $bond->status === 'validated' && (float) $bond->amount === 275.0
            && strtoupper(str_replace(' ', '', $bond->destination_iban)) === str_replace('-', '', MobilityBond::DESTINATION_IBAN)
            && str_contains(mb_strtolower($bond->concept), mb_strtolower($mobility->participant_name));
    }

    public function processDueNotifications(CarbonInterface $today): array
    {
        $created = [];
        foreach (Mobility::with(['application.documents', 'bond'])->get() as $mobility) {
            if ($mobility->start_date?->isSameDay($today->copy()->addDays(5)) && $mobility->application?->documentationComplete() && $this->bondIsValid($mobility)) {
                $this->validateSelectedParticipantBanking($mobility);
                $created[] = DomainNotification::firstOrCreate([
                    'mobility_id' => $mobility->id, 'type' => 'pocket_money_transfer', 'scheduled_for' => $today->toDateString(),
                ], ['message' => 'Preparar transferencia de pocket money: salida en cinco días.', 'status' => 'pending']);
            }
            if (in_array($mobility->participant_role, ['accompanying_teacher', 'job_shadowing_teacher'], true) && $mobility->end_date?->isSameDay($today->copy()->addDay())) {
                $created[] = DomainNotification::firstOrCreate([
                    'mobility_id' => $mobility->id, 'type' => 'final_report_reminder', 'scheduled_for' => $today->toDateString(),
                ], ['message' => 'Recordatorio: aportar memoria final antes de mañana.', 'status' => 'pending']);
            }
            if (in_array($mobility->participant_role, ['accompanying_teacher', 'job_shadowing_teacher'], true) && $mobility->end_date?->lt($today) && ! $mobility->application?->documents()->where('document_type', 'final_report')->where('status', 'validated')->exists()) {
                $mobility->status = MobilityStatus::PendingJustification;
                $mobility->save();
            }
        }

        return $created;
    }

    public function processWithdrawal(Mobility $mobility, array $data): MobilityWithdrawal
    {
        $withdrawal = $mobility->withdrawal()->updateOrCreate([], $data);
        $mobility->status = MobilityStatus::Cancelled;
        $mobility->save();
        if ($mobility->application) {
            $mobility->application->status = ApplicationStatus::Withdrawn;
            $mobility->application->save();
        }

        return $withdrawal;
    }
}
