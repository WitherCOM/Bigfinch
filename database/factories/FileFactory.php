<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<File>
 */
class FileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->word().'.pdf',
            'path' => 'bills/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1_000, 5_000_000),
        ];
    }

    public function image(): static
    {
        return $this->state(fn () => [
            'name' => fake()->word().'.png',
            'path' => 'bills/'.fake()->uuid().'.png',
            'mime_type' => 'image/png',
        ]);
    }
}
