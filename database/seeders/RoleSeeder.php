<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate([
            'name' => config('filament-shield.super_admin.name'),
            'guard_name' => 'web',
        ]);
        User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'test-admin@example.com',
            'can_manage_settings' => true,
            'password' => bcrypt('password'),
        ])->assignRole($adminRole);
        User::factory()->create([
            'name' => 'Test Normal',
            'email' => 'test-normal@example.com',
            'can_manage_settings' => false,
            'password' => bcrypt('password'),
        ]);
        $this->call(CurrencySeeder::class);
        Category::factory()->count(10)->create();
        Transaction::factory()->count(10000)->create();

    }
}
