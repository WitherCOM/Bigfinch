<?php

namespace App\Filament\Widgets;

use App\Enums\Direction;
use App\Enums\Flag;
use App\Models\Currency;
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
        })
        ->map(fn(Transaction $transaction) => [
            'date' => $transaction->date,
            'value' => $transaction->currency->nearestRate($transaction->date) * $transaction->value
        ]);
        for($i = 0; $i < 6; $i++) {
            $normal->add([
                'value' => 0,
                'date' => Carbon::today()->startOfMonth()->subMonths($i),
            ]);
        }
        $normal = $normal
        ->sortBy('date')
        ->groupBy(function ($data) {
            return $data['date']->startOfMonth();
        })->map(fn ($group) => $group->sum('value'));

        $investment = $transactions->filter(function (Transaction $transaction) {
            return $transaction->flags->doesntContain(Flag::INTERNAL_TRANSACTION) &&
            $transaction->flags->contains(Flag::INVESTMENT);
        })
        ->map(fn(Transaction $transaction) => [
            'date' => $transaction->date,
            'value' => $transaction->currency->nearestRate($transaction->date) * $transaction->value
        ]);
        for($i = 0; $i < 6; $i++) {
            $investment->add([
                'value' => 0,
                'date' => Carbon::today()->startOfMonth()->subMonths($i),
            ]);
        }
        $investment = $investment
        ->sortBy('date')
        ->groupBy(function ($data) {
            return $data['date']->startOfMonth();
        })->map(fn ($group) => $group->sum('value'));

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
