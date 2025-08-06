<?php

use App\Models\Scopes\OwnerScope;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new \App\Jobs\SyncCurrencies)->dailyAt('6:00');

Schedule::call(function () {
    $jobs = [];
    foreach(\App\Models\Integration::query()->where('can_auto_sync',true)->get() as $integration)
    {
        $jobs[] = new \App\Jobs\SyncTransactions($integration);
    }
    foreach(\App\Models\User::all() as $user)
    {
        $jobs[] = new \App\Jobs\RunFlagEngine($user->transactions()->where('date','>=', \Carbon\Carbon::now()->subDays(config('app.retro_days')))->get());
    }
    Bus::batch($jobs)->dispatch();
})->dailyAt('7:00');
