<?php

use App\Console\Commands\NotificarVencimientoPlazo;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(NotificarVencimientoPlazo::class)->dailyAt('08:00');
