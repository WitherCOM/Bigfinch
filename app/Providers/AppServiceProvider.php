<?php

namespace App\Providers;

use App\Services\NordigenService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(NordigenService::class, function($app) {
            return new NordigenService(config('gocardless.secret_id'), config('gocardless.secret_key'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'live')
        {
            URL::forceScheme('https');
        }
    }
}
