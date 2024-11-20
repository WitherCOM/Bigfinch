<?php

namespace App\Filament\Widgets;

use App\Enums\Direction;
use App\Models\Currency;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AverageOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $transactionExpend = Transaction::with(['currency','currency.rates'])->where('user_id',Auth::id())
            ->where('direction',Direction::EXPENSE->value)
            ->get();
        $dailyAverage = round($transactionExpend
            ->groupBy(fn (Transaction $transaction) => $transaction->date->toDateString())
            ->map(fn ($groups) => $groups->map(fn(Transaction $transaction) => $transaction->currency->nearestRate($transaction->date) * $transaction->value)->sum())
            ->avg());
        $monthAverage = round($transactionExpend
            ->groupBy(fn (Transaction $transaction) => $transaction->date->format('Y-m'))
            ->map(fn ($groups) => $groups->map(fn(Transaction $transaction) => $transaction->currency->nearestRate($transaction->date) * $transaction->value)->sum())
            ->avg());

        return [
            Stat::make('Daily Avg', Currency::where('iso_code', 'HUF')->first()->format($dailyAverage)),
            Stat::make('Monthly Avg', Currency::where('iso_code', 'HUF')->first()->format($monthAverage)),
        ];
    }
}
