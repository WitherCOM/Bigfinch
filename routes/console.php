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
    $batches = [];
    foreach(\App\Models\User::all() as $user)
    {
        $batches[] = Bus::batch($user->integrations()->where('can_auto_sync',true)->get()->map(function (\App\Models\Integration $integration) {
            return new \App\Jobs\SyncTransactions($integration);
        }));
        $batches = new \App\Jobs\RunFlagEngine($user->transactions()->where('date','>=', \Carbon\Carbon::now()->subDays(config('app.retro_days')))->get());
    }

    Bus::chain($batches)->dispatch();
})->dailyAt('7:00');
