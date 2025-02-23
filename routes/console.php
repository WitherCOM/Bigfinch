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
    foreach(\App\Models\Integration::query()->where('can_auto_sync',true)->get()->groupBy('user_id') as $user_id => $integrations)
    {
        foreach ($integrations as $integration) {
            \App\Jobs\SyncTransactions::dispatch($integration);
        }
        \App\Jobs\RunFlagEngine::dispatch(\App\Models\User::find($user_id)->transactions()->where('date','>=', \Carbon\Carbon::now()->subDays(90)));
    }
})->dailyAt('7:00');

Schedule::call(function () {
    foreach(\App\Models\Integration::query()->withoutGlobalScope(OwnerScope::class)->where('can_auto_sync',true)->get() as $integration)
    {
        \App\Jobs\SyncTransactions::dispatch($integration);
    }
})->dailyAt('22:00');



