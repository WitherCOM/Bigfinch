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

class CategoryAverage extends ChartWidget
{
    public function getHeading(): string|Htmlable|null
    {
        return __('Category average');
    }

    private const  MONTH_HISTORY = 6;

    protected function getData(): array
    {
        $displayCurrency = Currency::find(Auth::user()->default_currency_id);
        $transactions = Auth::user()->getStatisticalTransactionData(Carbon::today()->startOfMonth()->subMonths(3),Direction::EXPENSE,$displayCurrency)
            ->groupBy('category')
            ->mapWithKeys(fn($group,$category) => [$category => $group->groupBy(fn ($data) => $data['date']->startOfMonth())->avg(fn ($monthGroup) => $monthGroup->sum('value'))]);

        return [
            'datasets' => [
                [
                    'label' => __('Spending'),
                    'data' => $transactions->values()
                ]
            ],
            'labels' => $transactions->keys()
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
