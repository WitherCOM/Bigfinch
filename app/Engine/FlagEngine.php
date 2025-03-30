<?php

namespace App\Engine;

use App\Enums\Direction;
use App\Enums\Flag;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FlagEngine
{
    public static function run(Collection $transactions): Collection
    {
        // Runs on normal dataset
        for ($index = 0; $index < count($transactions); $index++) {
            $transactions[$index] = static::detectInvestor($transactions[$index]);
        }
        $transactions = static::detectInternalTransaction($transactions);
        return $transactions;
    }

    const DATE_THRESHOLD = 300;
    const VALUE_THRESHOLD = 5;
    /*
     * Have to be run a date oriented array
     *
     */
    public static function detectInternalTransaction(Collection $transactions): Collection
    {
        $dateSorted = $transactions->sortBy('date');
        for ($index = 0; $index < count($dateSorted)-1; $index++) {
            if (Carbon::parse($dateSorted[$index]->date)->diff(Carbon::parse($dateSorted[$index+1]->date))->seconds < self::DATE_THRESHOLD &&
                abs($dateSorted[$index]->value*$dateSorted[$index]->currency->rate - $dateSorted[$index+1]->value*$dateSorted[$index+1]->currency->rate) < self::VALUE_THRESHOLD &&
                (($dateSorted[$index]->direction == Direction::EXPENSE && $dateSorted[$index+1]->direction == Direction::INCOME) || ($dateSorted[$index+1]->direction == Direction::EXPENSE && $dateSorted[$index]->direction == Direction::INCOME))) {
                $dateSorted[$index]->flags[] = Flag::INTERNAL_TRANSACTION->value;
                $dateSorted[$index+1]->flags[] = Flag::INTERNAL_TRANSACTION->value;
            }
        }
        return $dateSorted;
    }

    const MATCH_INVESTORS = [
        'lightyear' // Lightyear Europe AS. Client Account.
    ];
    public static function detectInvestor(Transaction $transaction): Transaction
    {
        $regex = '/' . implode('|', self::MATCH_INVESTORS) . '/i';
        if (isset($transaction->open_banking_transaction['creditorName']) && preg_match($regex, $transaction->open_banking_transaction['creditorName']))
        {
            $transaction->flags[] = Flag::INVESTMENT;
        }
        return $transaction;
    }


}
