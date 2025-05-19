<?php

namespace App\Filament\Widgets;

use App\Enums\Direction;
use App\Enums\Flag;
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
        $displayCurrency = Currency::find(Auth::user()->default_currency_id);
        $transactionExpend = Transaction::with(['currency','currency.rates'])->where('user_id',Auth::id())
            ->where('date','>=',Carbon::now()->subMonths(3))
            ->whereJsonDoesntContain('flags', Flag::INTERNAL_TRANSACTION->value)
            ->whereJsonDoesntContain('flags', Flag::INVESTMENT->value)
            ->where('direction',Direction::EXPENSE->value)
            ->get();
        $dailyAverage = round($transactionExpend
            ->where('date','>=', Carbon::now()->subDays(3))
            ->groupBy(fn (Transaction $transaction) => $transaction->date->toDateString())
            ->map(fn ($groups) => $groups->map(fn(Transaction $transaction) => $transaction->currency->nearestRate($transaction->date) * $transaction->value / $displayCurrency->nearestRate($transaction->date))->sum())
            ->avg());
        $monthAverage = round($transactionExpend
            ->groupBy(fn (Transaction $transaction) => $transaction->date->format('Y-m'))
            ->map(fn ($groups) => $groups->map(fn(Transaction $transaction) => $transaction->currency->nearestRate($transaction->date) * $transaction->value / $displayCurrency->nearestRate($transaction->date))->sum())
            ->avg());

        return [
            Stat::make('Daily Avg', $displayCurrency->format($dailyAverage)),
            Stat::make('Monthly Avg', $displayCurrency->format($monthAverage)),
        ];
    }
}
