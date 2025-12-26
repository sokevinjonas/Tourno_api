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
Schedule::job(new AutoStartTournamentsJob)->everyFiveMinutes();
Schedule::job(new CheckFullTournamentsJob)->everyFiveMinutes();
Schedule::job(new CheckMatchDeadlinesJob)->everyTenMinutes();
Schedule::job(new SendMatchDeadlineWarningsJob)->everyFifteenMinutes();
