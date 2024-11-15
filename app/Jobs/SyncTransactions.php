<?php

namespace App\Jobs;

use App\Enums\Direction;
use App\Exceptions\GocardlessException;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Integration;
use App\Models\Merchant;
use App\Models\Rule;
use App\Models\Transaction;
use Filament\Notifications\Notification;
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
        $transactionCommonIds = $this->integration->all_transactions->pluck('common_id');
        try {
            $toCreate = $this->integration->getTransactions()->whereNotIn('transactionId', $transactionCommonIds)
                ->map(function ($transaction) use ($currencies, $rules) {
                    $data = [
                        'id' => Str::uuid(),
                        'description' => $this->getDescription($transaction),
                        'value' => abs(floatval($transaction['transactionAmount']['amount'])),
                        'direction' => floatval($transaction['transactionAmount']['amount']) > 0 ? Direction::INCOME->value : Direction::EXPENSE->value,
                        'date' => Carbon::parse($transaction['bookingDateTime'] ?? $transaction['bookingDate']),
                        'currency_id' => $currencies[$transaction['transactionAmount']['currency']],
                        'integration_id' => $this->integration->id,
                        'open_banking_transaction' => json_encode($transaction),
                        'user_id' => $this->integration->user_id,
                        'common_id' => $transaction['transactionId'],
                        'merchant_id' => Merchant::getMerchant($transaction, $this->integration->user_id)
                    ];
                    $data['category_id'] = $rules->filter(fn($rule) => $rule->checkRuleIsAppliedToData($data))->sortByDesc('priority')->first()?->target_id;
                    return $data;
                });
            Transaction::insert($toCreate->toArray());

            //Filter
            $excludeRules = Rule::exclude()->get();
            if ($excludeRules->count() > 0)
            {
                $softDeleteQuery = $this->integration->user->transactions()->query();
                foreach($excludeRules as $rule)
                {
                    $softDeleteQuery = $rule->excludeQueryFilter($softDeleteQuery);
                }
                $softDeleteQuery->delete();
            }


            Notification::make()
                ->title('Synced '.$this->integration->name)
                ->success()
                ->sendToDatabase($this->integration->user);
        } catch (GocardlessException $e)
        {
            Notification::make()
                ->title('Gocardless error')
                ->body($e->getMessage())
                ->danger()
                ->sendToDatabase($this->integration->user);
        }
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
}
