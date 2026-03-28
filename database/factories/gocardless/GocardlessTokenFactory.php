<?php

namespace Database\Factories\gocardless;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Integration>
 */
class GocardlessTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'secret_id' => Str::uuid(),
            'secret_key' => Str::random(40),
            'max_connections' => 50,
            'access_token' => Str::random(40),
            'refresh_token' => Str::random(40),
            'refresh_token_expires_at' => now()->addDays(30),
            'access_token_expires_at' => now()->addDays(30),
        ];
    }
}
