<?php

namespace App\Engine;

use App\Enums\Direction;
use App\Enums\Flag;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StaticEngine
{
    const DATE_THRESHOLD = 300;
    const VALUE_THRESHOLD = 0.001;
    public static function internalTransaction(Collection $transactions): Collection
    {
        $dateSorted = $transactions->sortBy('date');
        for ($i = 0; $i < count($dateSorted) - 1; $i++) {
            if ($dateSorted[$i]['date']->diff($dateSorted[$i+1]['date'])->seconds < self::DATE_THRESHOLD &&
                abs($dateSorted[$i]['value'] - $dateSorted[$i+1]['value']) < self::VALUE_THRESHOLD) {
                $transactionA = $dateSorted[$i];
                $transactionA ['direction'] = Direction::INTERNAL->value;
                $transactionB['flags'][] = Flag::INTERNAL_TRANSACTION->value;
                $transactionB = $dateSorted[$i+1];
                $transactionB['direction'] = Direction::INTERNAL->value;
                $transactionB['flags'][] = Flag::INTERNAL_TRANSACTION->value;
                $dateSorted[$i] = $transactionA;
                $dateSorted[$i+1] = $transactionB;
            }
        }
        return $dateSorted;
    }
}
