<?php

namespace App\Jobs;

use App\Enums\Direction;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Integration;
use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
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
        $currencies = Currency::all(['iso_code', 'id'])->pluck('id','iso_code');
        $transactionIds = $this->integration->all_transactions->pluck('id');
        $toCreate = $this->integration->getTransactions()->whereNotIn('common_id', $transactionIds)
            ->map(fn ($transaction) => [
                'id' => Str::uuid(),
                'description' => collect($transaction['remittanceInformationUnstructured'])->implode(' '),
                'value' => abs(floatval($transaction['transactionAmount']['amount'])),
                'direction' => floatval($transaction['transactionAmount']['amount']) > 0 ? Direction::INCOME : Direction::EXPENSE,
                'date' => Carbon::parse($transaction['bookingDate']),
                'currency_id' => $currencies[$transaction['transactionAmount']['currency']],
                'integration_id' => $this->integration->id,
                'user_id' => $this->integration->user_id,
                'common_id' => $transaction['transactionId'],
                'category_id' => Category::predict($transaction),
                'merchant_id' => $this->getMerchant($transaction)
            ])
            ->toArray();
        Transaction::insert($toCreate);
    }

    public function getMerchant(array $data)
    {
        if (Str::of($data['proprietaryBankTransactionCode'])->contains('CARD',true))
        {
            $merchant = Merchant::where('user_id',$this->integration->user_id)->where('name', $data['creditorName'])->first();
            if (is_null($merchant))
            {
                $merchant = new Merchant;
                $merchant->name = $data['creditorName'];
                $merchant->user_id = $this->integration->user_id;
                $merchant->save();
                $merchant->refresh();
            }
            return $merchant->id;
        }
        return null;
    }
}
