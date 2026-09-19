<?php

use App\Services\MobilityComplianceService;
use App\Services\MobilityOperationsService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(MobilityOperationsService::class)->processDueNotifications(now()))->dailyAt('08:00');
Schedule::call(fn () => app(MobilityComplianceService::class)->processDueChecks(now()))->dailyAt('08:10');
