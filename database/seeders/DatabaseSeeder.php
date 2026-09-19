<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\MobilityStatus;
use App\Enums\MobilityType;
use App\Enums\PaymentStatus;
use App\Models\Application;
use App\Models\EducationalCenter;
use App\Models\ErasmusProject;
use App\Models\HostPartner;
use App\Models\Mobility;
use App\Models\MobilityCall;
use App\Models\MobilityPayment;
use App\Models\User;
use App\Services\GrantCalculationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Centros Educativos del Consorcio (FP Comunitat Valenciana)
        $centers = [
            [
                'code' => '46016391',
                'name' => 'IES La Marxadella',
                'city' => 'Torrent',
                'province' => 'Valencia',
                'coordinator_name' => 'Rosaura Martí Gómez',
                'coordinator_email' => 'erasmus@marxadella.es',
                'coordinator_phone' => '+34 961 20 54 00',
                'is_active' => true,
                'notes' => 'Centro cabecera en familias de Informática, Electricidad y Automoción.',
            ],
            [
                'code' => '46018038',
                'name' => 'IES Campanar',
                'city' => 'Valencia',
                'province' => 'Valencia',
                'coordinator_name' => 'Vicent Soler Ferrandis',
                'coordinator_email' => 'v.soler@iescampanar.es',
                'coordinator_phone' => '+34 961 20 62 10',
                'is_active' => true,
                'notes' => 'Especializado en Sanidad, Cuidados Auxiliares y Farmacia.',
            ],
            [
                'code' => '46013107',
                'name' => 'CIPFP Mislata',
                'city' => 'Mislata',
                'province' => 'Valencia',
                'coordinator_name' => 'Carles Navarro Pérez',
                'coordinator_email' => 'c.navarro@cipfpmislata.com',
                'coordinator_phone' => '+34 961 20 59 25',
                'is_active' => true,
                'notes' => 'Centro Integrado de FP: Informática, Comercio y Marketing.',
            ],
            [
                'code' => '46022641',
                'name' => 'IES Conselleria',
                'city' => 'Valencia',
                'province' => 'Valencia',
                'coordinator_name' => 'Elena Beltrán Ortiz',
                'coordinator_email' => 'erasmus@iesconselleria.org',
                'coordinator_phone' => '+34 961 20 58 80',
                'is_active' => true,
                'notes' => 'Administración y Gestión, Servicios Socioculturales y a la Comunidad.',
            ],
            [
                'code' => '03014526',
                'name' => 'IES Gran Vía',
                'city' => 'Alicante',
                'province' => 'Alicante',
                'coordinator_name' => 'Javier Lledó Sanz',
                'coordinator_email' => 'internacional@iesgranvia.es',
                'coordinator_phone' => '+34 965 93 64 80',
                'is_active' => true,
                'notes' => 'Sede del consorcio en la provincia de Alicante.',
            ],
        ];

        $createdCenters = [];
        foreach ($centers as $data) {
            $createdCenters[] = EducationalCenter::create($data);
        }

        // 2. Usuarios de la plataforma
        User::create([
            'name' => 'Coordinador Consorcio Erasmus',
            'email' => 'admin@consorci.local',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'phone' => '+34 600 000 001',
        ]);

        User::create([
            'name' => 'Rosaura Martí (IES La Marxadella)',
            'email' => 'coordinador@marxadella.local',
            'password' => Hash::make('password'),
            'role' => 'center_coordinator',
            'educational_center_id' => $createdCenters[0]->id,
            'phone' => '+34 600 000 002',
        ]);

        // 3. Proyectos SEPIE / Fondos UE
        $p1 = ErasmusProject::create([
            'project_code' => '2024-1-ES01-KA121-VET-000215430',
            'title' => 'Consorci FP Comunitat Valenciana - Mobilitat Internacional 2024',
            'call_year' => '2024',
            'academic_year' => '2024-2025',
            'start_date' => '2024-06-01',
            'end_date' => '2025-11-30',
            'total_grant_awarded' => 125840.00,
            'individual_support_grant' => 84000.00,
            'travel_grant' => 18500.00,
            'organizational_support_grant' => 15340.00,
            'inclusion_support_grant' => 8000.00,
            'status' => 'active',
            'notes' => 'Acreditación Erasmus+ KA120-VET del consorcio. Subvención aprobada por SEPIE.',
        ]);

        $p2 = ErasmusProject::create([
            'project_code' => '2025-1-ES01-KA121-VET-000301124',
            'title' => 'Consorci FP Erasmus+ Oportunitats Formatives 2025',
            'call_year' => '2025',
            'academic_year' => '2025-2026',
            'start_date' => '2025-06-01',
            'end_date' => '2026-11-30',
            'total_grant_awarded' => 152000.00,
            'individual_support_grant' => 98000.00,
            'travel_grant' => 24000.00,
            'organizational_support_grant' => 18000.00,
            'inclusion_support_grant' => 12000.00,
            'status' => 'draft',
            'notes' => 'Proyecto aprobado para el curso académico 2025-2026.',
        ]);

        // 4. Convocatorias de Movilidad
        $call1 = MobilityCall::create([
            'erasmus_project_id' => $p1->id,
            'title' => 'Convocatòria FCT Grau Mitjà - Primavera 2025',
            'mobility_type' => MobilityType::VetStudentShort,
            'academic_year' => '2024-2025',
            'application_start_date' => '2024-10-15',
            'application_end_date' => '2024-11-30',
            'provisional_list_date' => '2024-12-10',
            'final_list_date' => '2024-12-20',
            'total_vacancies' => 12,
            'status' => 'resolved',
            'requirements' => 'Estar matriculat en 2n curs de cicle formatiu de grau mitjà en els centres del consorci. Nivell mínim recomanat de llengua estrangera B1. Expedient acadèmic del 1r curs.',
        ]);

        $call2 = MobilityCall::create([
            'erasmus_project_id' => $p1->id,
            'title' => 'Convocatòria ErasmusPro Llarga Durada (Recents Titulats)',
            'mobility_type' => MobilityType::VetStudentLong,
            'academic_year' => '2024-2025',
            'application_start_date' => '2024-11-01',
            'application_end_date' => '2025-01-15',
            'provisional_list_date' => '2025-01-25',
            'final_list_date' => '2025-02-05',
            'total_vacancies' => 6,
            'status' => 'open',
            'requirements' => 'Estades de 90 dies en empreses europees per a titulats en els últims 12 mesos.',
        ]);

        $call3 = MobilityCall::create([
            'erasmus_project_id' => $p1->id,
            'title' => 'Convocatòria Mobilitat Personal Docent (Job Shadowing)',
            'mobility_type' => MobilityType::StaffTraining,
            'academic_year' => '2024-2025',
            'application_start_date' => '2024-10-01',
            'application_end_date' => '2025-03-01',
            'provisional_list_date' => '2025-03-10',
            'final_list_date' => '2025-03-15',
            'total_vacancies' => 5,
            'status' => 'open',
            'requirements' => 'Estades de formació o observació de bones pràctiques (Job Shadowing) de 5 dies en centres de FP europeus.',
        ]);

        // 5. Socios y Empresas Europeas de Acogida
        $partners = [
            [
                'name' => 'Informatica & Web Solutions Italia S.r.l.',
                'vat_number' => 'IT08492019482',
                'country_code' => 'IT',
                'country_group' => 1,
                'city' => 'Bolonia',
                'address' => "Via dell'Indipendenza 42, 40121 Bologna",
                'contact_person' => 'Matteo Rossi',
                'contact_email' => 'm.rossi@websolutions.it',
                'contact_phone' => '+39 051 445 981',
                'sector' => 'Informática y Desarrollo Web',
                'working_languages' => ['Inglés', 'Italiano'],
                'notes' => 'Excelente empresa para alumnos de SMR y DAM.',
            ],
            [
                'name' => 'TechBerlin Innovations GmbH',
                'vat_number' => 'DE318492015',
                'country_code' => 'DE',
                'country_group' => 2,
                'city' => 'Berlín',
                'address' => 'Friedrichstraße 114, 10117 Berlin',
                'contact_person' => 'Karin Weber',
                'contact_email' => 'k.weber@techberlin.de',
                'contact_phone' => '+49 30 2094 550',
                'sector' => 'Sistemas y Ciberseguridad',
                'working_languages' => ['Inglés', 'Alemán'],
                'notes' => 'Tutor bilingüe, proyectos punteros en redes y cloud.',
            ],
            [
                'name' => 'Dublin Health & Care Services Ltd.',
                'vat_number' => 'IE9824018A',
                'country_code' => 'IE',
                'country_group' => 1,
                'city' => 'Dublín',
                'address' => 'Grand Canal Dock, Dublin 2',
                'contact_person' => "Sean O'Connor",
                'contact_email' => 'sean@dublinhealth.ie',
                'contact_phone' => '+353 1 492 8841',
                'sector' => 'Sanidad y Cuidados Auxiliares',
                'working_languages' => ['Inglés'],
                'notes' => 'Convenio activo para estudiantes de TCAE y Farmacia.',
            ],
            [
                'name' => 'Atelier Logistique & Commerce Paris',
                'vat_number' => 'FR84920183049',
                'country_code' => 'FR',
                'country_group' => 1,
                'city' => 'París',
                'address' => 'Boulevard Saint-Germain 75, 75005 Paris',
                'contact_person' => 'Camille Dupont',
                'contact_email' => 'c.dupont@logistique-paris.fr',
                'contact_phone' => '+33 1 43 25 80 12',
                'sector' => 'Comercio Internacional y Logística',
                'working_languages' => ['Francés', 'Inglés'],
                'notes' => 'Prácticas de gestión de stocks y distribución.',
            ],
            [
                'name' => 'Lisboa Digital Hub Lda.',
                'vat_number' => 'PT509283746',
                'country_code' => 'PT',
                'country_group' => 2,
                'city' => 'Lisboa',
                'address' => 'Avenida da Liberdade 245, 1250-142 Lisboa',
                'contact_person' => 'Tiago Silva',
                'contact_email' => 'tiago@lisboadigital.pt',
                'contact_phone' => '+351 21 345 6789',
                'sector' => 'Marketing Digital y Diseño Web',
                'working_languages' => ['Portugués', 'Inglés', 'Español'],
                'notes' => 'Acogida muy valorada por el alumnado valenciano.',
            ],
        ];

        $createdPartners = [];
        foreach ($partners as $pData) {
            $createdPartners[] = HostPartner::create($pData);
        }

        // 6. Candidaturas / Baremos de Estudiantes
        $candidates = [
            [
                'first_name' => 'Marc',
                'last_name' => 'Gimeno Albiach',
                'doc' => '48712345K',
                'email' => 'marc.gimeno@alumnat.es',
                'phone' => '+34 611 223 344',
                'center_idx' => 0,
                'vocational' => 'Técnico en Sistemas Microinformáticos y Redes',
                'lang_level' => 'B2',
                'academic' => 8.75,
                'lang_score' => 3.50,
                'faculty' => 4.50,
                'interview' => 4.25,
                'inclusion' => 0.00,
                'status' => ApplicationStatus::Admitted,
            ],
            [
                'first_name' => 'Laia',
                'last_name' => 'Pons Ferrer',
                'doc' => '53982147M',
                'email' => 'laia.pons@alumnat.es',
                'phone' => '+34 622 334 455',
                'center_idx' => 1,
                'vocational' => 'Técnico en Cuidados Auxiliares de Enfermería',
                'lang_level' => 'B1',
                'academic' => 9.20,
                'lang_score' => 2.50,
                'faculty' => 4.80,
                'interview' => 4.50,
                'inclusion' => 1.00,
                'status' => ApplicationStatus::Admitted,
            ],
            [
                'first_name' => 'Pau',
                'last_name' => 'Navarro Castelló',
                'doc' => '20948271F',
                'email' => 'pau.navarro@alumnat.es',
                'phone' => '+34 633 445 566',
                'center_idx' => 2,
                'vocational' => 'Técnico en Gestión Administrativa',
                'lang_level' => 'B1',
                'academic' => 8.10,
                'lang_score' => 2.50,
                'faculty' => 4.00,
                'interview' => 4.00,
                'inclusion' => 0.00,
                'status' => ApplicationStatus::Admitted,
            ],
            [
                'first_name' => 'Aitana',
                'last_name' => 'Soriano Bellver',
                'doc' => '44892105R',
                'email' => 'aitana.soriano@alumnat.es',
                'phone' => '+34 644 556 677',
                'center_idx' => 0,
                'vocational' => 'Técnico en Instalaciones Eléctricas y Automáticas',
                'lang_level' => 'B2',
                'academic' => 8.90,
                'lang_score' => 3.50,
                'faculty' => 4.70,
                'interview' => 4.60,
                'inclusion' => 0.00,
                'status' => ApplicationStatus::Admitted,
            ],
            [
                'first_name' => 'Joan',
                'last_name' => 'Ribera Climent',
                'doc' => '24910283T',
                'email' => 'joan.ribera@alumnat.es',
                'phone' => '+34 655 667 788',
                'center_idx' => 3,
                'vocational' => 'Técnico en Actividades Comerciales',
                'lang_level' => 'A2',
                'academic' => 7.50,
                'lang_score' => 1.00,
                'faculty' => 3.80,
                'interview' => 3.75,
                'inclusion' => 0.00,
                'status' => ApplicationStatus::Reserve,
            ],
            [
                'first_name' => 'Marta',
                'last_name' => 'Vidal Sanchis',
                'doc' => '54019283L',
                'email' => 'marta.vidal@alumnat.es',
                'phone' => '+34 666 778 899',
                'center_idx' => 4,
                'vocational' => 'Técnico en Farmacia y Parafarmacia',
                'lang_level' => 'C1',
                'academic' => 9.50,
                'lang_score' => 4.50,
                'faculty' => 4.90,
                'interview' => 4.80,
                'inclusion' => 0.00,
                'status' => ApplicationStatus::Admitted,
            ],
            [
                'first_name' => 'Sergi',
                'last_name' => 'Alonso Benlloch',
                'doc' => '49201928P',
                'email' => 'sergi.alonso@alumnat.es',
                'phone' => '+34 677 889 900',
                'center_idx' => 2,
                'vocational' => 'Técnico en Sistemas Microinformáticos y Redes',
                'lang_level' => 'B1',
                'academic' => 7.20,
                'lang_score' => 2.50,
                'faculty' => 3.50,
                'interview' => 3.50,
                'inclusion' => 0.00,
                'status' => ApplicationStatus::Scored,
            ],
            [
                'first_name' => 'Carla',
                'last_name' => 'Montesinos Esteve',
                'doc' => '73928104Q',
                'email' => 'carla.montesinos@alumnat.es',
                'phone' => '+34 688 990 011',
                'center_idx' => 1,
                'vocational' => 'Técnico en Atención a Personas en Situación de Dependencia',
                'lang_level' => 'B1',
                'academic' => 8.40,
                'lang_score' => 2.50,
                'faculty' => 4.20,
                'interview' => 4.00,
                'inclusion' => 1.00,
                'status' => ApplicationStatus::Submitted,
            ],
        ];

        $createdApps = [];
        foreach ($candidates as $cData) {
            $total = (float) $cData['academic'] + (float) $cData['lang_score'] + (float) $cData['faculty'] + (float) $cData['interview'] + (float) $cData['inclusion'];

            $app = Application::create([
                'mobility_call_id' => $call1->id,
                'educational_center_id' => $createdCenters[$cData['center_idx']]->id,
                'first_name' => $cData['first_name'],
                'last_name' => $cData['last_name'],
                'id_document_type' => 'DNI',
                'id_document_number' => $cData['doc'],
                'email' => $cData['email'],
                'phone' => $cData['phone'],
                'birth_date' => '2004-03-15',
                'vocational_program' => $cData['vocational'],
                'level' => 'grado_medio',
                'language_certificate_level' => $cData['lang_level'],
                'academic_score' => $cData['academic'],
                'language_score' => $cData['lang_score'],
                'faculty_report_score' => $cData['faculty'],
                'interview_score' => $cData['interview'],
                'inclusion_factor_score' => $cData['inclusion'],
                'total_score' => round($total, 2),
                'status' => $cData['status'],
                'motivation_letter' => "Molt interessat en fer l'estada de pràctiques en una empresa europea puntera per millorar la meva competència lingüística i professional.",
            ]);

            $createdApps[] = $app;
        }

        // 7. Movilidades y Estancias Efectivas
        $calcService = new GrantCalculationService;

        // Movilidad 1: Marc en Bolonia (Italia) - En estancia activa
        $calc1 = $calcService->calculate('IT', 30, 'standard', false, 'student');
        $m1 = Mobility::create([
            'erasmus_project_id' => $p1->id,
            'mobility_call_id' => $call1->id,
            'application_id' => $createdApps[0]->id,
            'host_partner_id' => $createdPartners[0]->id,
            'educational_center_id' => $createdCenters[0]->id,
            'participant_name' => 'Marc Gimeno Albiach',
            'participant_email' => 'marc.gimeno@alumnat.es',
            'participant_type' => 'student',
            'destination_country' => 'IT',
            'destination_city' => 'Bolonia',
            'start_date' => now()->subDays(12),
            'end_date' => now()->addDays(18),
            'duration_days' => 30,
            'travel_type' => 'standard',
            'fewer_opportunities' => false,
            'daily_grant_rate' => $calc1['daily_rate'],
            'individual_support_amount' => $calc1['individual_support'],
            'travel_amount' => $calc1['travel_amount'],
            'inclusion_amount' => $calc1['inclusion_amount'],
            'total_grant_amount' => $calc1['total_grant'],
            'status' => MobilityStatus::InProgress,
            'learning_agreement_signed' => true,
            'grant_agreement_signed' => true,
            'tutor_in_origin' => 'Rosaura Martí',
            'tutor_in_host' => 'Matteo Rossi',
            'notes' => 'Estancia iniciada satisfactoriamente en Bolonia.',
        ]);

        // Pagos para Movilidad 1: Anticipo abonado, final pendiente
        MobilityPayment::create([
            'mobility_id' => $m1->id,
            'payment_type' => 'first_payment_80',
            'amount' => $calc1['first_payment_80'],
            'scheduled_date' => now()->subDays(20),
            'paid_date' => now()->subDays(15),
            'status' => PaymentStatus::Paid,
            'reference_number' => 'ANT80-ERASMUS-2024-001',
            'notes' => 'Abono del 80% confirmado tras firma de convenio.',
        ]);

        MobilityPayment::create([
            'mobility_id' => $m1->id,
            'payment_type' => 'final_balance_20',
            'amount' => $calc1['final_balance_20'],
            'scheduled_date' => now()->addDays(25),
            'status' => PaymentStatus::Pending,
            'reference_number' => 'LIQ20-ERASMUS-2024-001',
            'notes' => 'Liquidación final prevista a la vuelta.',
        ]);

        // Movilidad 2: Laia en Dublín (Irlanda) - Con inclusión y Green travel
        $calc2 = $calcService->calculate('IE', 30, 'green_travel', true, 'student');
        $m2 = Mobility::create([
            'erasmus_project_id' => $p1->id,
            'mobility_call_id' => $call1->id,
            'application_id' => $createdApps[1]->id,
            'host_partner_id' => $createdPartners[2]->id,
            'educational_center_id' => $createdCenters[1]->id,
            'participant_name' => 'Laia Pons Ferrer',
            'participant_email' => 'laia.pons@alumnat.es',
            'participant_type' => 'student',
            'destination_country' => 'IE',
            'destination_city' => 'Dublín',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(25),
            'duration_days' => 30,
            'travel_type' => 'green_travel',
            'fewer_opportunities' => true,
            'daily_grant_rate' => $calc2['daily_rate'],
            'individual_support_amount' => $calc2['individual_support'],
            'travel_amount' => $calc2['travel_amount'],
            'inclusion_amount' => $calc2['inclusion_amount'],
            'total_grant_amount' => $calc2['total_grant'],
            'status' => MobilityStatus::InProgress,
            'learning_agreement_signed' => true,
            'grant_agreement_signed' => true,
            'tutor_in_origin' => 'Vicent Soler',
            'tutor_in_host' => "Sean O'Connor",
        ]);

        MobilityPayment::create([
            'mobility_id' => $m2->id,
            'payment_type' => 'first_payment_80',
            'amount' => $calc2['first_payment_80'],
            'scheduled_date' => now()->subDays(10),
            'paid_date' => now()->subDays(7),
            'status' => PaymentStatus::Paid,
            'reference_number' => 'ANT80-ERASMUS-2024-002',
        ]);

        MobilityPayment::create([
            'mobility_id' => $m2->id,
            'payment_type' => 'final_balance_20',
            'amount' => $calc2['final_balance_20'],
            'scheduled_date' => now()->addDays(35),
            'status' => PaymentStatus::Pending,
            'reference_number' => 'LIQ20-ERASMUS-2024-002',
        ]);

        // Movilidad 3: Aitana en Berlín (Alemania) - Próxima salida / Convenio firmado
        $calc3 = $calcService->calculate('DE', 30, 'standard', false, 'student');
        $m3 = Mobility::create([
            'erasmus_project_id' => $p1->id,
            'mobility_call_id' => $call1->id,
            'application_id' => $createdApps[3]->id,
            'host_partner_id' => $createdPartners[1]->id,
            'educational_center_id' => $createdCenters[0]->id,
            'participant_name' => 'Aitana Soriano Bellver',
            'participant_email' => 'aitana.soriano@alumnat.es',
            'participant_type' => 'student',
            'destination_country' => 'DE',
            'destination_city' => 'Berlín',
            'start_date' => now()->addDays(14),
            'end_date' => now()->addDays(44),
            'duration_days' => 30,
            'travel_type' => 'standard',
            'fewer_opportunities' => false,
            'daily_grant_rate' => $calc3['daily_rate'],
            'individual_support_amount' => $calc3['individual_support'],
            'travel_amount' => $calc3['travel_amount'],
            'inclusion_amount' => $calc3['inclusion_amount'],
            'total_grant_amount' => $calc3['total_grant'],
            'status' => MobilityStatus::Contracted,
            'learning_agreement_signed' => true,
            'grant_agreement_signed' => true,
            'tutor_in_origin' => 'Rosaura Martí',
            'tutor_in_host' => 'Karin Weber',
        ]);

        MobilityPayment::create([
            'mobility_id' => $m3->id,
            'payment_type' => 'first_payment_80',
            'amount' => $calc3['first_payment_80'],
            'scheduled_date' => now()->addDays(5),
            'status' => PaymentStatus::Approved,
            'reference_number' => 'ANT80-ERASMUS-2024-003',
        ]);

        // Movilidad 4: Marta Vidal en Lisboa (Portugal) - Planificada
        $calc4 = $calcService->calculate('PT', 30, 'standard', false, 'student');
        Mobility::create([
            'erasmus_project_id' => $p1->id,
            'mobility_call_id' => $call1->id,
            'application_id' => $createdApps[5]->id,
            'host_partner_id' => $createdPartners[4]->id,
            'educational_center_id' => $createdCenters[4]->id,
            'participant_name' => 'Marta Vidal Sanchis',
            'participant_email' => 'marta.vidal@alumnat.es',
            'participant_type' => 'student',
            'destination_country' => 'PT',
            'destination_city' => 'Lisboa',
            'start_date' => now()->addDays(28),
            'end_date' => now()->addDays(58),
            'duration_days' => 30,
            'travel_type' => 'standard',
            'fewer_opportunities' => false,
            'daily_grant_rate' => $calc4['daily_rate'],
            'individual_support_amount' => $calc4['individual_support'],
            'travel_amount' => $calc4['travel_amount'],
            'inclusion_amount' => $calc4['inclusion_amount'],
            'total_grant_amount' => $calc4['total_grant'],
            'status' => MobilityStatus::Planned,
            'learning_agreement_signed' => true,
            'grant_agreement_signed' => false,
            'tutor_in_origin' => 'Javier Lledó',
            'tutor_in_host' => 'Tiago Silva',
        ]);

        // Movilidad 5: Profesorado Job Shadowing en París (Francia)
        $calc5 = $calcService->calculate('FR', 5, 'green_travel', false, 'staff');
        Mobility::create([
            'erasmus_project_id' => $p1->id,
            'mobility_call_id' => $call3->id,
            'host_partner_id' => $createdPartners[3]->id,
            'educational_center_id' => $createdCenters[3]->id,
            'participant_name' => 'Elena Beltrán Ortiz (Docente)',
            'participant_email' => 'erasmus@iesconselleria.org',
            'participant_type' => 'staff',
            'destination_country' => 'FR',
            'destination_city' => 'París',
            'start_date' => now()->subDays(60),
            'end_date' => now()->subDays(55),
            'duration_days' => 5,
            'travel_type' => 'green_travel',
            'fewer_opportunities' => false,
            'daily_grant_rate' => $calc5['daily_rate'],
            'individual_support_amount' => $calc5['individual_support'],
            'travel_amount' => $calc5['travel_amount'],
            'inclusion_amount' => 0,
            'total_grant_amount' => $calc5['total_grant'],
            'status' => MobilityStatus::Completed,
            'learning_agreement_signed' => true,
            'grant_agreement_signed' => true,
            'certificate_of_attendance' => true,
            'eu_survey_completed' => true,
            'tutor_in_origin' => 'Consorci Erasmus',
            'tutor_in_host' => 'Camille Dupont',
            'notes' => 'Job Shadowing finalizado con éxito en París. Documentación completa en SEPIE.',
        ]);

        $this->call(DemoInterviewsSeeder::class);
    }
}
