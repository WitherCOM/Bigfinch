<?php

namespace App\Providers;

use App\Models\AutoTag;
use App\Models\Transaction;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

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

        FilamentAsset::register([
            Js::make('app-js', Vite::asset('resources/js/app.js'))->module(),
        ]);

        Transaction::creating(function (Transaction $transaction) {
            $transaction->tags = array_merge($transaction->tags ?? [], AutoTag::where('user_id', Auth::id())
                ->active()
                ->pluck('tag')
                ->toArray());
        });
    }
}
