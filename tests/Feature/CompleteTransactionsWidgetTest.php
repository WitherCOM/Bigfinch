<?php

namespace Tests\Feature;

use App\Enums\Direction;
use App\Filament\Widgets\CompleteTransactions;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompleteTransactionsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_transaction_form_is_prefilled_with_merchant(): void
    {
        (new CurrencySeeder)->run();
        $user = User::factory()->create();
        Category::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => null,
            'direction' => Direction::EXPENSE->value,
            'date' => now(),
            'merchant' => 'Test Merchant',
        ]);
        $this->actingAs($user);
        Livewire::actingAs($user);

        Livewire::test(CompleteTransactions::class)
            ->mountTableAction('book_transaction', $transaction)
            ->assertTableActionDataSet([
                'merchant' => 'Test Merchant',
                'description' => $transaction->description,
            ]);
    }
}
