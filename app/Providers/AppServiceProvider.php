<?php

namespace App\Providers;

use App\Models\AutoTag;
use App\Models\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
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

        Transaction::creating(function (Transaction $transaction) {
            $transaction->tags = array_merge($transaction->tags, AutoTag::where('user_id', Auth::id())
                ->active()
                ->pluck('tag')
                ->toArray());
        });
    }
}
