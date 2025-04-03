<?php

namespace App\Filament\Widgets;

use App\Enums\Direction;
use App\Enums\Flag;
use App\Models\Transaction;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MonthlyDistribution extends ChartWidget
{
    public function getHeading(): string|Htmlable|null
    {
        return __('Monthly Distribution');
    }

    protected function getData(): array
    {
        $transactions = Transaction::with(['currency','currency.rates'])
            ->where('user_id',Auth::id())
            ->where('direction', Direction::EXPENSE->value)
            ->where('date','>=',Carbon::today()->startOfMonth()->subMonths(6))
            ->orderBy('date')
            ->get();

        $normal = $transactions->filter(function (Transaction $transaction) {
           return $transaction->flags->doesntContain(Flag::INTERNAL_TRANSACTION) &&
           $transaction->flags->doesntContain(Flag::INVESTMENT);
        })->groupBy(function (Transaction $transaction) {
            return $transaction->date->startOfMonth();
        })->map(fn ($groups) => $groups->map(fn(Transaction $transaction) => $transaction->currency->nearestRate($transaction->date) * $transaction->value)->sum());

        $investment = $transactions->filter(function (Transaction $transaction) {
            return $transaction->flags->doesntContain(Flag::INTERNAL_TRANSACTION) &&
            $transaction->flags->contains(Flag::INVESTMENT);
        })->groupBy(function (Transaction $transaction) {
            return $transaction->date->startOfMonth();
        })->map(fn ($groups) => $groups->map(fn(Transaction $transaction) => $transaction->currency->nearestRate($transaction->date) * $transaction->value)->sum());

        return [
            'datasets' => [
                [
                    'label' => __('Normal'),
                    'data' => $normal->values(),
                    'borderColor' => '#b31010',
                    'backgroundColor' => '#d11919'
                ],
                [
                    'label' => __('Investment'),
                    'data' => $investment->values()
                ]
            ],
            'labels' => $normal->keys()->map(fn ($date) => Carbon::parse($date)->monthName),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        {
            scales: {
                y: {
                    ticks: {
                        callback: (value) => value + ' Ft',
                    },
                },
            },
        }
    JS);
    }

}
