<?php

namespace App\Engine;

use App\Enums\Direction;
use App\Models\Currency;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OpenBankingDataParser
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
                if (self::calculateShannonEntropy($name) < 3) {
                    $newWords[] = Str::of($word)->lower()->ucfirst()->toString();
                }
            }
            $name = implode(' ', $newWords);
            return $name;
        }
        return $name;
    }

    private static function calculateShannonEntropy($word) {
        $length = strlen($word);
        if ($length === 0) {
            return 0; // Avoid division by zero
        }

        $frequencies = count_chars($word, 1); // Get frequency of each character
        $entropy = 0.0;

        foreach ($frequencies as $char => $count) {
            $probability = $count / $length; // Probability of each character
            $entropy -= $probability * log($probability, 2); // Shannon entropy formula
        }

        return $entropy * 100;
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
