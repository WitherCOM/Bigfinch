<?php

namespace App\Engine;

use App\Enums\Direction;
use App\Enums\Flag;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StaticEngine
{
    const DATE_THRESHOLD = 300;
    const VALUE_THRESHOLD = 5;
    public static function internalTransaction(Collection $transactions): Collection
    {
        $currencies = Currency::all();
        $dateSorted = $transactions->sortBy('date');
        for ($i = 0; $i < count($dateSorted) - 1; $i++) {
            if (Carbon::parse($dateSorted[$i]['date'])->diff(Carbon::parse($dateSorted[$i+1]['date']))->seconds < self::DATE_THRESHOLD &&
                abs($dateSorted[$i]['value']*$currencies->find($dateSorted[$i]['currency_id'])->rate - $dateSorted[$i+1]['value']*$currencies->find($dateSorted[$i+1]['currency_id'])->rate) < self::VALUE_THRESHOLD &&
                (($dateSorted[$i]['direction'] == Direction::EXPENSE->value && $dateSorted[$i+1]['direction'] == Direction::INCOME->value) || ($dateSorted[$i+1]['direction'] == Direction::EXPENSE->value && $dateSorted[$i]['direction'] == Direction::INCOME->value))) {
                $transactionA = $dateSorted[$i];
                $transactionA['direction'] = Direction::INTERNAL->value;
                $transactionB['flags'][] = Flag::INTERNAL_TRANSACTION->value;
                if ($transactionA['direction'] == Direction::EXPENSE->value)
                {
                    $transactionA['value'] = - abs($transactionA['value']);
                }
                $transactionB = $dateSorted[$i+1];
                $transactionB['direction'] = Direction::INTERNAL->value;
                $transactionB['flags'][] = Flag::INTERNAL_TRANSACTION->value;
                if ($transactionB['direction'] == Direction::EXPENSE->value)
                {
                    $transactionB['value'] = - abs($transactionA['value']);
                }
                $dateSorted[$i] = $transactionA;
                $dateSorted[$i+1] = $transactionB;
            }
        }
        return $dateSorted;
    }
}
