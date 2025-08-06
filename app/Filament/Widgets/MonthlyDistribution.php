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

    private const  MONTH_HISTORY = 6;

    protected function getData(): array
    {
        $displayCurrency = Currency::find(Auth::user()->default_currency_id);
        $expenseTransactions = Auth::user()->getStatisticalTransactionData(Carbon::today()->startOfMonth()->subMonths(self::MONTH_HISTORY),Direction::EXPENSE,$displayCurrency);
        $investmentTransactions = Auth::user()->getStatisticalTransactionData(Carbon::today()->startOfMonth()->subMonths(self::MONTH_HISTORY),Direction::INVESTMENT,$displayCurrency);
        // Fill empty months
        for($i = 0; $i <= self::MONTH_HISTORY; $i++) {
            $expenseTransactions->add([
                'value' => 0,
                'date' => Carbon::today()->startOfMonth()->subMonths($i),
            ]);
            $investmentTransactions->add([
                'value' => 0,
                'date' => Carbon::today()->startOfMonth()->subMonths($i),
            ]);
        }
        $groupExpense = $expenseTransactions
        ->sortBy('date')
        ->groupBy(function ($data) {
            return $data['date']->startOfMonth();
        })->map(fn ($group) => $group->sum('value'));

        $groupInvest = $investmentTransactions
        ->sortBy('date')
        ->groupBy(function ($data) {
            return $data['date']->startOfMonth();
        })->map(fn ($group) => $group->sum('value'));

        return [
            'datasets' => [
                [
                    'label' => __('Normal'),
                    'data' => $groupExpense->values(),
                    'borderColor' => '#b31010',
                    'backgroundColor' => '#d11919'
                ],
                [
                    'label' => __('Investment'),
                    'data' => $groupInvest->values()
                ]
            ],
            'labels' => $groupExpense->keys()->map(fn ($date) => Carbon::parse($date)->monthName),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): RawJs
    {
        $displayCurrency = Currency::find(Auth::user()->default_currency_id);
        $placeholder = $displayCurrency->format("<replace>");
        return RawJs::make("
        {
            scales: {
                y: {
                    ticks: {
                        callback: (value) => '$placeholder'.replace('<replace>',value),
                    },
                },
            },
        }
    ");
    }

}
