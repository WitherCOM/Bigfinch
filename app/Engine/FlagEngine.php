<?php

namespace App\Engine;

use App\Enums\Direction;
use App\Enums\Flag;
use App\Models\Integration;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    const INTERNAL_TO_KEYWORDS = [
        'topup',
        'top-up'
    ];
    const INTERNAL_FROM_KEYWORDS = [];
    /*
     *
     */
    public static function detectInternalTransaction(Collection $transactions): Collection
    {
        $integrations = Integration::all();
        for ($index = 0; $index < count($transactions); $index++) {
            if (!is_null($transactions[$index]->integration_id)) {
                $creditorName = Str::lower($transactions[$index]->open_banking_transaction['creditorName'] ?? "");
                $debitorName = Str::lower($transactions[$index]->open_banking_transaction['debitorName'] ?? "");
                $info = Str::lower(implode(" ", $transactions[$index]->open_banking_transaction["remittanceInformationUnstructuredArray"]));
                if ($transactions[$index] == Direction::INCOME && Str::contains($info, self::INTERNAL_TO_KEYWORDS)) {
                    $transactions[$index]->direction = Direction::INTERNAL_TO;
                    continue;
                }
                if ($transactions[$index] == Direction::EXPENSE && Str::contains($info, self::INTERNAL_FROM_KEYWORDS)) {
                    $transactions[$index]->direction = Direction::INTERNAL_FROM;
                    continue;
                }
                $debitorCreditorIntegration = null;
                foreach($integrations->where('user_id',$transactions[$index]->user_id) as $integration) {
                    if (Str::contains($debitorName,$integration->institution_name) || Str::contains($creditorName,$integration->institution_name)) {
                        $debitorCreditorIntegration = $integration;
                        break;
                    }
                }
                if (!is_null($debitorCreditorIntegration) && $debitorCreditorIntegration->id != $transactions[$index]->integration_id) {
                    if ($transactions[$index]->direction == Direction::EXPENSE) {
                        $transactions[$index]->direction = Direction::INTERNAL_FROM;
                        continue;
                    } else if ($transactions[!$index]->direction == Direction::INCOME) {
                        $transactions[$index]->direction = Direction::INTERNAL_TO;
                        continue;
                    }
                }
            }
        }
        return $transactions;
    }

    const MATCH_INVESTORS = [
        'lightyear', // Lightyear Europe AS. Client Account.
        'webkincstar' // Magyar államkincstar
    ];
    public static function detectInvestor(Transaction $transaction): Transaction
    {
        $regex = '/' . implode('|', self::MATCH_INVESTORS) . '/i';
        if (isset($transaction->open_banking_transaction['creditorName']) && preg_match($regex, $transaction->open_banking_transaction['creditorName']))
        {
            $transaction->direction = Direction::INVESTMENT;
        }
        return $transaction;
    }


}
