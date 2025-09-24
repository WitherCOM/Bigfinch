<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Integration>
 */
class IntegrationFactory extends Factory
{
    public function withAccountCount($count): Factory
    {
        return $this->state(function (array $attributes) use ($count) {
            return [
                'accounts' => Collection::range(0,$count-1)->map(fn ($value) => Str::uuid())->toArray(),
            ];
        });

    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'name' => $this->faker->word,
            'user_id' => User::all()->random()->id,
            'accounts' => Collection::range(0,rand(0,10))->map(fn ($value) => Str::uuid())->toArray(),
            'institution_id' => 'id',
            'institution_name' => 'name',
            'institution_logo' => 'logo',
            'requisition_id' => Str::uuid()
        ];
    }
}
