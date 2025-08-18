<?php

namespace App\Filament\Actions\Transactions;

use Filament\Actions\BulkAction;
use App\Enums\Direction;
use App\Models\Transaction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MergeBulkAction extends BulkAction
{
    public function setUp(): void
    {
        parent::setUp();

        $this->action(function (Collection $records) {
            // Find expense
            $expenses = $records->filter(fn(Transaction $record) => $record->direction === Direction::EXPENSE);
            $incomes = $records->filter(fn(Transaction $record) => $record->direction === Direction::INCOME);
            if ($expenses->count() == 1 && $incomes->count() >= 1) {
                $sumIncome = $incomes->sum('value');
                $expense = $expenses->first();
                if ($expense->value > $sumIncome) {
                    $expense->value -= $sumIncome;
                    $expense->save();
                    $incomes->each(fn(Transaction $transaction) => $transaction->delete());
                }
            }
        });

    }
}
