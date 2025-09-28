<?php

namespace App\Filament\Widgets;

use App\Enums\Direction;
use App\Enums\Flag;
use App\Models\Currency;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StatOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $displayCurrency = Currency::find(Auth::user()->default_currency_id);
        $transactions = Auth::user()->getStatisticalTransactionData(Carbon::now()->subMonths(3),Direction::EXPENSE,$displayCurrency);
        $expenseTransactions = Auth::user()->getStatisticalTransactionData(\Illuminate\Support\Carbon::today()->startOfMonth(),Direction::EXPENSE,$displayCurrency);
        $dailyAverage = round($transactions
            ->where('date','>=', Carbon::now()->subDays(3))
            ->groupBy(fn (array $transaction) => $transaction['date']->toDateString())
            ->map(fn ($groups) => $groups->sum('value'))
            ->avg());
        $monthAverage = round($transactions
            ->groupBy(fn (array $transaction) => $transaction['date']->format('Y-m'))
            ->map(fn ($groups) => $groups->sum('value'))
            ->avg());

        $thisMonth = $expenseTransactions->sum('value');

        return [
            Stat::make('Daily Avg', $displayCurrency->format($dailyAverage)),
            Stat::make('Monthly Avg', $displayCurrency->format($monthAverage)),
            Stat::make('This month', $displayCurrency->format($thisMonth)),
        ];
    }
}
