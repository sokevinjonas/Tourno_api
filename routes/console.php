<?php

use App\Jobs\AutoStartTournamentsJob;
use App\Jobs\CheckFullTournamentsJob;
use App\Jobs\CheckMatchDeadlinesJob;
use App\Jobs\SendMatchDeadlineWarningsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule jobs
// Démarrage des tournois - chaque minute pour réactivité
Schedule::job(new AutoStartTournamentsJob)->everyMinute();

// Vérification des tournois complets - toutes les 5 minutes est OK
Schedule::job(new CheckFullTournamentsJob)->everyFiveMinutes();

// Vérification des deadlines expirées - plus fréquent pour réactivité
Schedule::job(new CheckMatchDeadlinesJob)->everyFiveMinutes();

// Envoi des avertissements de deadline - toutes les 15 minutes et 30 minutes est suffisant
Schedule::job(new SendMatchDeadlineWarningsJob)->everyFiveMinutes();
