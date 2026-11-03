<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate([
            'name' => config('filament-shield.super_admin.name'),
            'guard_name' => 'web',
        ]);
        User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'test-admin@example.com',
            'password' => bcrypt('password'),
        ])->assignRole($adminRole);
        User::factory()->create([
            'name' => 'Test Normal',
            'email' => 'test-normal@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->call(CurrencySeeder::class);
        Category::factory()->count(10)->create();
        Transaction::factory()->count(10000)->create();

    }
}
