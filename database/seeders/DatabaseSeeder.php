<?php

namespace Database\Seeders;

use App\Enums\Direction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->call(CurrencySeeder::class);
        Category::factory()->count(10)->create([
            'direction' => Direction::EXPENSE->value
        ]);
        Transaction::factory()->count(10000)->create();


    }
}
