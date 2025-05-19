<?php

use App\Models\Scopes\OwnerScope;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new \App\Jobs\SyncCurrencies)->dailyAt('6:00');

Schedule::call(function () {
    foreach(\App\Models\Integration::query()->where('can_auto_sync',true)->get() as $integration)
    {
        \App\Jobs\SyncTransactions::dispatch($integration);
    }
})->dailyAt('7:00');

Schedule::call(function () {
    foreach(\App\Models\User::all() as $user)
    {
        \App\Jobs\RunFlagEngine::dispatch($user->transactions()->where('date','>=', \Carbon\Carbon::now()->subDays(90)));
    }
})->dailyAt('7:30');


