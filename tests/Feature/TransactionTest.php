<?php

namespace Tests\Feature;

use App\Enums\Direction;
use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $user->user_timezone = 'Europe/Budapest';
        $user->save();
        $this->actingAs($user);
        Livewire::actingAs($user);
        (new CurrencySeeder)->run();
    }

    public function test_create_transaction_manually(): void
    {
        Livewire::test(CreateTransaction::class)
            ->assertSuccessful();
        Livewire::test(CreateTransaction::class)
            ->fillForm([
                'description' => 'Test Transaction',
                'value' => 300,
                'currency_id' => Currency::all()->random()->id,
                'direction' => Direction::EXPENSE->value,
                'date' => '2019-01-01 10:00:00',
                'category_id' => Category::factory()->create()->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
        $this->assertDatabaseHas('transactions', [
            'description' => 'Test Transaction',
            'date' => '2019-01-01 09:00:00',
        ]);
        $this->assertEquals('2019-01-01 09:00:00', Transaction::first()->date->format('Y-m-d H:i:s'));
    }

    public function test_update_transaction_without_date_modify(): void
    {
        Category::factory()->create();
        $originalDate = '2019-01-01 09:00:00';
        $transaction = Transaction::factory()->create();
        $transaction->date = $originalDate;
        $transaction->save();
        Livewire::test(EditTransaction::class, [
            'record' => $transaction->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet(['date' => $originalDate])
            ->fillForm([
                'description' => 'EditTransaction',
            ])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertDatabaseHas('transactions', [
            'description' => 'EditTransaction',
            'date' => $originalDate,
        ]);
    }
}
