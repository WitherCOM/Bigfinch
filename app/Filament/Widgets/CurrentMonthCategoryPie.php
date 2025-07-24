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
        return __('Monthly category');
    }


    protected function getData(): array
    {

        $displayCurrency = Currency::find(Auth::user()->default_currency_id);
        $transactions = Transaction::with(['currency', 'currency.rates'])
            ->where('user_id', Auth::id())
            ->where('direction', Direction::EXPENSE->value)
            ->where('date', '>=', Carbon::today()->startOfMonth())
            ->orderBy('date')
            ->get();

        $data = $transactions->filter(function (Transaction $transaction) {
            return $transaction->flags->doesntContain(Flag::INTERNAL_TRANSACTION) &&
                $transaction->flags->doesntContain(Flag::INVESTMENT) &&
                $transaction->flags->doesntContain(Flag::EXCHANGE);
        })
            ->map(fn(Transaction $transaction) => [
                'category' => $transaction->category?->name ?? __('Other'),
                'value' => $transaction->currency->nearestRate($transaction->date) * $transaction->value / $displayCurrency->nearestRate($transaction->date)
            ])
            ->sortBy('date')
            ->groupBy('category')
            ->mapWithKeys(fn($group,$category_id) => [$category_id => $group->sum('value')]);

        return [
            'datasets' => [
                [
                    'label' => __('Normal'),
                    'data' => $data->values(),
                ]
            ],
            'labels' => $data->keys()
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
