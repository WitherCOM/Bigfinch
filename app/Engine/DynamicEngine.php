<?php

namespace App\Engine;

use App\Enums\Direction;
use App\Enums\Flag;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DynamicEngine
{
    const DATE_THRESHOLD = 300;
    const VALUE_THRESHOLD = 5;

    public static function run(Collection $transactions): Collection {
        $records = static::internalTransaction($transactions);
        return $records;
    }
    public static function internalTransaction(Collection $transactions): Collection
    {
        $dateSorted = $transactions->sortBy('date');
        for ($i = 0; $i < count($dateSorted) - 1; $i++) {
            if (Carbon::parse($dateSorted[$i]->date)->diff(Carbon::parse($dateSorted[$i+1]->date))->seconds < self::DATE_THRESHOLD &&
                abs($dateSorted[$i]->value*$dateSorted[$i]->currency->rate - $dateSorted[$i+1]->value*$dateSorted[$i+1]->currency->rate) < self::VALUE_THRESHOLD &&
                (($dateSorted[$i]->direction == Direction::EXPENSE->value && $dateSorted[$i+1]->direction == Direction::INCOME->value) || ($dateSorted[$i+1]->direction == Direction::EXPENSE->value && $dateSorted[$i]->direction == Direction::INCOME->value))) {
                $dateSorted[$i]->flags[] = Flag::INTERNAL_TRANSACTION->value;
                $dateSorted[$i+1]->flags[] = Flag::INTERNAL_TRANSACTION->value;
            }
        }
        return $dateSorted;
    }
}
