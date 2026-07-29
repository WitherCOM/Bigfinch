<?php

namespace Database\Factories;

use App\Enums\CurrencyPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'iso_code' => $this->faker->countryCode(),
            'position' => collect(CurrencyPosition::cases())->random(),
            'symbol' => $this->faker->currencyCode(),
        ];
    }
}
