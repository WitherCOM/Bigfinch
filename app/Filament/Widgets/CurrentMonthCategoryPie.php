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

class CurrentMonthCategoryPie extends ChartWidget
{
    public function getHeading(): string|Htmlable|null
    {
        return __('Current month category');
    }


    protected function getData(): array
    {
        $displayCurrency = Currency::find(Auth::user()->default_currency_id);
        $transactions = Auth::user()->getStatisticalTransactionData(Carbon::today()->startOfMonth(),Direction::EXPENSE,$displayCurrency)
            ->groupBy('category')
            ->mapWithKeys(fn($group,$category) => [$category => $group->sum('value')]);

        return [
            'datasets' => [
                [
                    'label' => __('Normal'),
                    'data' => $transactions->values(),
                ]
            ],
            'labels' => $transactions->keys()
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
