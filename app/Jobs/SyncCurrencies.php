<?php

namespace App\Jobs;

use SoapClient;
use App\Models\Currency;
use App\Models\CurrencyRate;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class SyncCurrencies implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $codes = Currency::all()->pluck('id','iso_code');
        $soap_client = new SoapClient("http://www.mnb.hu/arfolyamok.asmx?wsdl");
        $raw_rates = $soap_client->GetCurrentExchangeRates()->GetCurrentExchangeRatesResult;
        $rates = collect([]);
        foreach(simplexml_load_string($raw_rates)->Day->Rate as $rate)
        {
            $rates->add(['value' => floatval($rate), 'currency' => Str::of($rate->attributes()['curr'])->toString()]);
        }
        CurrencyRate::insert(
            $rates->whereIn('currency',$codes->keys())
                ->map(fn($item) => [
                    'id' => Str::uuid(),
                    'currency_id' => $codes[$item['currency']],
                    'rate_to_huf' => $item['value'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ])
                ->toArray()
        );
    }
}
