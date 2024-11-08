<?php

namespace Database\Seeders;

use App\Enums\CurrencyPosition;
use App\Models\Category;
use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::create([
            'iso_code' => 'HUF',
            'position' => CurrencyPosition::SUFFIX,
            'symbol' => 'Ft',
            'rate_to_huf' => 1
        ]);
        Currency::create([
            'iso_code' => 'EUR',
            'position' => CurrencyPosition::PREFIX,
            'symbol' => '€',
            'rate_to_huf' => 1
        ]);
        Currency::create([
            'iso_code' => 'USD',
            'position' => CurrencyPosition::PREFIX,
            'symbol' => '$',
            'rate_to_huf' => 1
        ]);
        Currency::create([
            'iso_code' => 'PLN',
            'position' => CurrencyPosition::SUFFIX,
            'symbol' => 'zł',
            'rate_to_huf' => 1
        ]);
        Currency::create([
            'iso_code' => 'CZK',
            'position' => CurrencyPosition::SUFFIX,
            'symbol' => 'Kč',
            'rate_to_huf' => 1
        ]);
    }
}
