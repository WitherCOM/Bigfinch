<?php

namespace App\Jobs;

use App\Enums\Direction;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Integration;
use App\Models\Merchant;
use App\Models\Rule;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SyncTransactions implements ShouldQueue
{
    use Queueable;

    private Integration $integration;

    /**
     * Create a new job instance.
     */
    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $currencies = Currency::all(['iso_code', 'id'])->pluck('id', 'iso_code');
        $rules = Rule::category()->get();
        $transactionIds = $this->integration->all_transactions->pluck('id');
        $toCreate = $this->integration->getTransactions()->whereNotIn('common_id', $transactionIds)
            ->map(function ($transaction) use ($currencies, $rules) {
                $data = [
                    'id' => Str::uuid(),
                    'description' => $this->getDescription($transaction),
                    'value' => abs(floatval($transaction['transactionAmount']['amount'])),
                    'direction' => floatval($transaction['transactionAmount']['amount']) > 0 ? Direction::INCOME->value : Direction::EXPENSE->value,
                    'date' => Carbon::parse($transaction['bookingDate']),
                    'currency_id' => $currencies[$transaction['transactionAmount']['currency']],
                    'integration_id' => $this->integration->id,
                    'open_banking_transaction' => json_encode($transaction),
                    'user_id' => $this->integration->user_id,
                    'common_id' => $transaction['transactionId'],
                    'merchant_id' => $this->getMerchant($transaction)
                ];
                $data['category_id'] = $rules->filter(fn($rule) => $rule->checkRuleIsAppliedToData($data))->sortByDesc('priority')->first()?->target_id;
                return $data;
            });
        foreach(Rule::exclude()->get() as $rule)
        {
            $toCreate = $rule->excludeCollectionFilter($toCreate);
        }
        Transaction::insert($toCreate->toArray());
    }

    public function getDescription(array $data)
    {
        $name = Str::of($data['proprietaryBankTransactionCode'])->lower()->camel()->title()->toString();
        if (array_key_exists('additionalInformation',$data))
        {
            $name .= " " . $data['additionalInformation'];
        }
        if (array_key_exists('remittanceInformationUnstructuredArray',$data))
        {
            $name .= " " . implode(" ", $data['remittanceInformationUnstructuredArray']);
        }
        if (array_key_exists('remittanceInformationUnstructured',$data))
        {
            $name .= " " . $data['remittanceInformationUnstructured'];
        }
        return $name;
    }

    public function getMerchant(array $data)
    {
        $value = floatval($data['transactionAmount']['amount']);
        if ($value > 0 && array_key_exists('debtorName',$data) && !array_key_exists('creditorName',$data))
        {
            $name = $data['debtorName'];
        }
        else if ($value < 0 && array_key_exists('creditorName',$data) && !array_key_exists('debtorName',$data))
        {
            $name = $data['creditorName'];
        }
        else
        {
            $name = null;
        }
        if (!is_null($name))
        {
            $merchant = Merchant::where('user_id', $this->integration->user_id)->whereJsonContains('search_keys', $name)->first();
            if (is_null($merchant)) {
                $merchant = new Merchant;
                $merchant->name = $name;
                $merchant->search_keys = [$name];
                $merchant->user_id = $this->integration->user_id;
                $merchant->save();
                $merchant->refresh();
            }
            return $merchant->id;
        }

        return null;
    }
}
