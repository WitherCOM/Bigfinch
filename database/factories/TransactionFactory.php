<?php

namespace Database\Factories;

use App\Enums\Direction;
use App\Models\Category;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence(),
            'direction' => collect([Direction::EXPENSE->value, Direction::INCOME->value])->random(),
            'value' => $this->faker->randomFloat(2, 1000, 50000),
            'currency_id' => Currency::where('iso_code','HUF')->first()->id,
            'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'merchant' => $this->faker->name(),
            'category_id' => Category::all()->random()->id,
            'common_id' => $this->faker->uuid(),
            'user_id' => User::all()->random()->id,
        ];
    }
}
