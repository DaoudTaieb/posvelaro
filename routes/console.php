<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tâches planifiées Golden Pos
|--------------------------------------------------------------------------
| Exécutées par « php artisan schedule:run », lancé chaque minute par le
| Planificateur de tâches Windows (voir deploy/planifier_taches.bat).
*/

// Contrôle de toutes les pages chaque matin avant l'ouverture du magasin
Schedule::command('app:smoke-test')
    ->dailyAt('07:30')
    ->appendOutputTo(storage_path('logs/smoke-test.log'));
