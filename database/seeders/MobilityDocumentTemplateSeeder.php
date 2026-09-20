<?php

namespace Database\Seeders;

use App\Models\MobilityDocumentTemplate;
use Illuminate\Database\Seeder;

class MobilityDocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $template) {
            MobilityDocumentTemplate::query()->updateOrCreate(
                ['profile' => $template['profile'], 'phase' => $template['phase'], 'document_type' => $template['document_type']],
                ['label' => $template['label'], 'required' => true],
            );
        }
    }

    private function templates(): array
    {
        $student = [
            ['PRE', 'learning_agreement', 'Learning Agreement / confirmación EWP'],
            ['PRE', 'grant_agreement', 'Convenio de subvención firmado'],
            ['PRE', 'seguro_accidentes_rc', 'Seguro de accidentes y responsabilidad civil'],
            ['PRE', 'tarjeta_sanitaria_europea', 'Tarjeta Sanitaria Europea o seguro equivalente'],
            ['PRE', 'evaluacion_ols_inicio', 'Justificante de evaluación inicial OLS'],
            ['PRE', 'control_pago_30_dias', 'Control interno de prefinanciación'],
            ['DURANTE', 'confirmation_of_arrival', 'Certificado de llegada'],
            ['DURANTE', 'cambios_learning_agreement', 'Cambios del Learning Agreement, si aplica'],
            ['DURANTE', 'solicitud_ampliacion', 'Solicitud de ampliación, si aplica'],
            ['POST', 'traineeship_certificate', 'Certificado de prácticas / transcripción de notas'],
            ['POST', 'participant_report', 'Confirmación de Participant Report UE'],
            ['POST', 'justificantes_viaje_ecologico', 'Justificantes de viaje ecológico, si aplica'],
            ['POST', 'reconocimiento_academico', 'Reconocimiento académico / Europass'],
            ['POST', 'pago_liquidacion_final', 'Control interno de pago de liquidación final'],
        ];
        $staff = [
            ['PRE', 'mobility_agreement_staff', 'Acuerdo de movilidad firmado por las tres partes'],
            ['PRE', 'convenio_subvencion_staff', 'Convenio financiero firmado'],
            ['PRE', 'seguro_viaje_profesor', 'Seguro de viaje y comisión de servicio'],
            ['POST', 'certificate_of_attendance', 'Certificado de estancia con fechas reales'],
            ['POST', 'participant_report_staff', 'Cuestionario final UE'],
            ['POST', 'memoria_difusion', 'Memoria de difusión interna'],
        ];

        return collect([
            ...array_map(fn (array $row) => $this->row('student_gs_eche', $row), $student),
            ...array_map(fn (array $row) => $this->row('student_gm_stepv', $row), $student),
            ...array_map(fn (array $row) => $this->row('staff', $row), $staff),
        ])->all();
    }

    private function row(string $profile, array $row): array
    {
        return ['profile' => $profile, 'phase' => $row[0], 'document_type' => $row[1], 'label' => $row[2]];
    }
}
