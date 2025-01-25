<?php

namespace App\Engine;

use App\Enums\Direction;
use App\Enums\Flag;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StaticEngine
{
    public static function internalTransaction(Collection $transactions): Collection
    {
        $dateSorted = $transactions->sortBy('date');
        for ($i = 0; $i < count($dateSorted) - 1; $i++) {
            if ($dateSorted[$i]['date']->diff($dateSorted[$i+1]['date'])->seconds < 5 * 60 &&
                abs($dateSorted[$i]['value'] - $dateSorted[$i+1]['value']) < 5) {
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
