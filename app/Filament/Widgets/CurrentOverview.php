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

class CurrentOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $displayCurrency = Currency::find(Auth::user()->default_currency_id);
        $expenseTransactions = Auth::user()->getStatisticalTransactionData(\Illuminate\Support\Carbon::today()->startOfMonth(),Direction::EXPENSE,$displayCurrency);
        $thisMonth = $expenseTransactions->sum('value');

        return [
            Stat::make('This month', $displayCurrency->format($thisMonth)),
        ];
    }
}
