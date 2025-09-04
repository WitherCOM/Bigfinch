<?php

namespace App\Filament\Widgets;

use App\Enums\Direction;
use App\Models\Currency;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TendChart extends ChartWidget
{
    protected ?string $heading = 'Tend Chart';

    protected function getData(): array
    {
        $displayCurrency = Currency::find(Auth::user()->default_currency_id);
        $expenseTransactions = Auth::user()->getStatisticalTransactionData(Carbon::today()->startOfYear(),Direction::EXPENSE,$displayCurrency);
        $incomeTransactions = Auth::user()->getStatisticalTransactionData(Carbon::today()->startOfYear(),Direction::INCOME,$displayCurrency);
        // Fill empty months
        for($i = 0; $i < Carbon::today()->month; $i++) {
            $expenseTransactions->add([
                'value' => 0,
                'date' => Carbon::today()->startOfMonth()->subMonths($i),
            ]);
            $incomeTransactions->add([
                'value' => 0,
                'date' => Carbon::today()->startOfMonth()->subMonths($i),
            ]);
        }
        $groupExpense = $expenseTransactions
            ->sortBy('date')
            ->groupBy(function ($data) {
                return $data['date']->startOfMonth();
            })->map(fn ($group) => $group->sum('value'));

        $groupIncome = $incomeTransactions
            ->sortBy('date')
            ->groupBy(function ($data) {
                return $data['date']->startOfMonth();
            })->map(fn ($group) => $group->sum('value'));

        $diffs = $groupIncome->map(fn ($value, $key) => $value - $groupExpense[$key])->values();
        $positive = $diffs->map(fn ($value) => $value > 0 ? $value : 0);
        $negative = $diffs->map(fn ($value) => $value < 0 ? $value : 0);
        $labels = $groupExpense->keys()->map(fn ($date) => Carbon::parse($date)->monthName);

        return [
            'datasets' => [
                [
                    'label' => __('Positive'),
                    'backgroundColor' => "#00ff00",
                    'data' => $positive,
                ],
                [
                    'label' => __('Negative'),
                    'backgroundColor' => "#ff0000",
                    'data' => $negative,
                ]
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
