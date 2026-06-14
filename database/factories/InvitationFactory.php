<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email' => fake()->unique()->safeEmail(),
            'token' => Str::random(25),
            'valid_until' => now()->addDays(3),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'valid_until' => now()->subDay(),
        ]);
    }
}
