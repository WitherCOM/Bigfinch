<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new \App\Jobs\SyncCurrencies)->dailyAt('6:00');

foreach(\App\Models\Integration::all() as $integration)
{
    Schedule::job(\App\Jobs\SyncTransactions::dispatch($integration))->dailyAt('7:00');
}
