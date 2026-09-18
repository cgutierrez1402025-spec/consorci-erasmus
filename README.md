# Consorci Erasmus

Plataforma de gestión integral para **Consorcios Erasmus+ de Formación Profesional y Educación** (proyectos KA121/KA120 y KA131 acreditados por el SEPIE y la Comisión Europea).

Inspirada en los patrones arquitectónicos de `kdp-author-manager-v6`, esta aplicación implementa un panel administrativo moderno con **Laravel 12**, **Filament 3**, **Livewire 3**, **Tailwind CSS** y soporte para bases de datos **SQLite / MySQL**.

---

## Características Principales

1. **Centros Educativos del Consorcio (`EducationalCenters`):**
   - Registro de institutos y centros integrados de FP participantes.
   - Coordinadores/as Erasmus por centro, datos de contacto y estado de adhesión.
2. **Proyectos y Fondos SEPIE / UE (`ErasmusProjects`):**
   - Control de convocatorias anuales (KA121-VET, KA122, etc.).
   - Desglose presupuestario aprobado: apoyo individual, viajes, apoyo organizativo (OS) y suplementos de inclusión.
3. **Convocatorias de Movilidad (`MobilityCalls`):**
   - Modalidades: FP Grado Medio (cortas y ErasmusPro), FP Básica, FP Grado Superior y Personal Docente (Job Shadowing / Cursos).
   - Publicación de plazos, requisitos y plazas ofertadas.
   - **Adjudicación automática:** acción en un clic para resolver la convocatoria, clasificar por baremo y asignar plazas y suplentes.
4. **Candidaturas y Baremación (`Applications`):**
   - Ficha del solicitante con ciclo formativo y documentación.
   - Baremación desglosada y ponderada: competencia lingüística (A2-C2), expediente académico, informe del equipo educativo, entrevista/motivación y factor de inclusión.
   - Conversión directa de candidaturas admitidas en movilidades efectivas.
5. **Empresas y Socios Europeos de Acogida (`HostPartners`):**
   - Directorio de entidades de acogida en Italia, Alemania, Irlanda, Francia, Portugal, etc.
   - Clasificación por Grupo de País SEPIE (Grupo 1, 2 y 3), sectores profesionales e idiomas de trabajo.
6. **Movilidades y Estancias (`Mobilities`):**
   - Fechas, duración y cálculo automático de subvención mediante `GrantCalculationService` (dieta diaria según grupo de país, viaje estándar vs. *Green Travel*, y suplemento de inclusión).
   - Seguimiento documental: Learning Agreement, Convenio de subvención, Certificado de Estancia (*Attendance*) y encuesta final de la UE (*EU Survey*).
   - Generación automática del calendario de pagos (80% anticipo y 20% liquidación final).
7. **Control Económico y Pagos (`MobilityPayments`):**
   - Calendario y tracking de abonos con referencias bancarias/contables.
8. **Dashboard Interactivo con Métricas en Tiempo Real:**
   - Estadísticas globales de centros, participantes, presupuesto ejecutado y selección.
   - Tabla de estancias activas y próximas salidas.
   - Gráfico de distribución de participantes por país de destino.
   - Últimas solicitudes registradas.

---

## Requisitos

- **PHP 8.2+** (probado con PHP 8.4 bajo Laravel Herd).
- **Composer 2+**.
- **Node.js 18+** y npm (para Vite).
- **SQLite** (configurado por defecto) o **MySQL/PostgreSQL**.

---

## Puesta en Marcha Rápida

1. **Clonar o acceder al directorio:**
   ```bash
   cd /Users/mauriog.pelaez/consorci-erasmus
   ```

2. **Instalar dependencias:**
   ```bash
   composer install
   npm install
   ```

3. **Configurar el entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Base de datos y datos demo:**
   ```bash
   touch database/database.sqlite
   php artisan migrate:fresh --seed
   ```

5. **Compilar activos:**
   ```bash
   npm run build
   ```

6. **Iniciar el servidor de desarrollo:**
   ```bash
   php artisan serve
   ```

---

## Acceso al Panel de Administración

- **URL:** `http://127.0.0.1:8000/admin`
- **Administrador del Consorcio (SuperAdmin):**
  - **Email:** `admin@consorci.local`
  - **Contraseña:** `password`
- **Coordinador de Centro Educativo (IES La Marxadella):**
  - **Email:** `coordinador@marxadella.local`
  - **Contraseña:** `password`

---

## Ejecución de Pruebas

```bash
php artisan test
```

## Estructura del Código

- `app/Enums`: Enumeraciones tipadas (`MobilityType`, `ApplicationStatus`, `MobilityStatus`, `CountryGroup`, `PaymentStatus`).
- `app/Models`: Modelos de dominio Eloquent con relaciones y casts.
- `app/Services`: Lógica de negocio especializada:
  - `GrantCalculationService`: Tablas oficiales SEPIE y cálculo de dietas/viaje/inclusión.
  - `ApplicationScoringService`: Algoritmo de baremación y resolución de convocatorias.
- `app/Filament/Resources`: Recursos CRUD con formularios modulares, filtros y acciones personalizadas.
- `app/Filament/Widgets`: Widgets analíticos del panel de control.
- `database/seeders`: Generador de datos de prueba realistas basados en centros y convocatorias de la Comunitat Valenciana.
