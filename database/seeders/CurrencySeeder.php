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
            'symbol' => 'Ft'
        ]);
        Currency::create([
            'iso_code' => 'EUR',
            'position' => CurrencyPosition::PREFIX,
            'symbol' => '€',
        ]);
        Currency::create([
            'iso_code' => 'USD',
            'position' => CurrencyPosition::PREFIX,
            'symbol' => '$'
        ]);
        Currency::create([
            'iso_code' => 'PLN',
            'position' => CurrencyPosition::SUFFIX,
            'symbol' => 'zł'
        ]);
        Currency::create([
            'iso_code' => 'CZK',
            'position' => CurrencyPosition::SUFFIX,
            'symbol' => 'Kč'
        ]);
    }
}
