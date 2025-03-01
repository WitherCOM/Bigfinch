<?php

namespace App\Engine;

use App\Enums\Direction;
use App\Models\Currency;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OpenBankingEngine
{

    private static function getDescription(array $data): string
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

    public static function getMerchantName(array $data) : string | null
    {
        $value = floatval($data['transactionAmount']['amount']);
        if ($value > 0 && array_key_exists('debtorName',$data))
        {
            $name = $data['debtorName'];
        }
        else if ($value < 0 && array_key_exists('creditorName',$data))
        {
            $name = $data['creditorName'];
        }
        else
        {
            $name = null;
        }
        if (!is_null($name))
        {
            $newWords = [];
            foreach (explode(' ', $name) as $word) {
                $newWords[] = Str::of($word)->lower()->ucfirst()->toString();
            }
            $name = implode(' ', $newWords);
            return $name;
        }
        return $name;
    }

    public static function parse(array $openBankingData): array {
        $currencies = Currency::all(['iso_code', 'id'])->pluck('id', 'iso_code');
        return [
            'description' => self::getDescription($openBankingData),
            'value' => abs(floatval($openBankingData['transactionAmount']['amount'])),
            'direction' => floatval($openBankingData['transactionAmount']['amount']) > 0 ? Direction::INCOME->value : Direction::EXPENSE->value,
            'date' => Carbon::parse($openBankingData['bookingDateTime'] ?? $openBankingData['bookingDate']),
            'currency_id' => $currencies[$openBankingData['transactionAmount']['currency']],
            'open_banking_transaction' => json_encode($openBankingData),
            'common_id' => $openBankingData['transactionId'],
            'merchant' => self::getMerchantName($openBankingData),
        ];
    }
}
