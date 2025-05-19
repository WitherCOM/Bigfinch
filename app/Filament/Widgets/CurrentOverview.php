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
        $thisMonth = Transaction::with(['currency','currency.rates'])->where('user_id',Auth::id())
            ->where('date','>=',Carbon::now()->startOfMonth())
            ->whereJsonDoesntContain('flags', Flag::INTERNAL_TRANSACTION->value)
            ->whereJsonDoesntContain('flags', Flag::INVESTMENT->value)
            ->where('direction',Direction::EXPENSE->value)
            ->get()
            ->map(fn (Transaction $transaction) => $transaction->currency->nearestRate($transaction->date) * $transaction->value / $displayCurrency->nearestRate($transaction->date))
            ->sum();

        return [
            Stat::make('This month', $displayCurrency->format($thisMonth)),
        ];
    }
}
