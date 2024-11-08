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
            'iso_format' => 'HUF',
            'position' => CurrencyPosition::SUFFIX,
            'symbol' => 'Ft',
            'rate_to_huf' => 1
        ]);
        Currency::create([
            'iso_format' => 'EUR',
            'position' => CurrencyPosition::PREFIX,
            'symbol' => '€',
            'rate_to_huf' => 1
        ]);
        Currency::create([
            'iso_format' => 'USD',
            'position' => CurrencyPosition::PREFIX,
            'symbol' => '$',
            'rate_to_huf' => 1
        ]);
        Currency::create([
            'iso_format' => 'PLN',
            'position' => CurrencyPosition::SUFFIX,
            'symbol' => 'zł',
            'rate_to_huf' => 1
        ]);
        Currency::create([
            'iso_format' => 'CZK',
            'position' => CurrencyPosition::SUFFIX,
            'symbol' => 'Kč',
            'rate_to_huf' => 1
        ]);
    }
}
