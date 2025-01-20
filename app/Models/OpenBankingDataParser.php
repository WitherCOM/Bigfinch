<?php

namespace App\Models;

use App\Enums\Direction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OpenBankingDataParser
{

    private static function getDescription(array $data)
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
    public static function parse(Integration $integration, array $openBankingData): array {
        $currencies = Currency::all(['iso_code', 'id'])->pluck('id', 'iso_code');
        return [
            'id' => Str::uuid(),
            'description' => self::getDescription($openBankingData),
            'value' => abs(floatval($openBankingData['transactionAmount']['amount'])),
            'direction' => floatval($openBankingData['transactionAmount']['amount']) > 0 ? Direction::INCOME->value : Direction::EXPENSE->value,
            'date' => Carbon::parse($openBankingData['bookingDateTime'] ?? $openBankingData['bookingDate']),
            'currency_id' => $currencies[$openBankingData['transactionAmount']['currency']],
            'integration_id' => $integration->id,
            'open_banking_transaction' => json_encode($openBankingData),
            'user_id' => $integration->user_id,
            'common_id' => $openBankingData['transactionId'],
            'merchant_id' => Merchant::getMerchant($openBankingData)?->id,
        ];
    }
}
