<?php

namespace App\Rules;

use ResourceBundle;
use Illuminate\Translation\PotentiallyTranslatedString;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CurrencyCodeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string):PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $bundle = ResourceBundle::create('en', 'ICUDATA-curr');
        $currencies = $bundle->get('Currencies');

        if (!(collect($currencies)->keys()->contains($value)))
        {
            $fail(__('validation.currency.code',compact('attribute')));
        }

    }
}
