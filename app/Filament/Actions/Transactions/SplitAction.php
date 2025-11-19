<?php

namespace App\Filament\Actions\Transactions;

use Filament\Actions\Action;
use App\Models\Transaction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SplitAction extends Action
{
    public function setUp(): void
    {
        parent::setUp();
        $this->form([
            TextInput::make('value')
                ->required()
                ->minValue(0)
                ->maxValue(fn(Transaction $record) => $record->value)
                ->numeric(),
            TextInput::make('description')
                ->afterStateHydrated(function (TextInput $component, Transaction $transaction) {
                    $component->state($transaction->description);
                })
                ->required(),
            Select::make('category_id')
                ->afterStateHydrated(function (Select $component, Transaction $transaction) {
                    $component->state($transaction->category_id);
                })
                ->preload()
                ->relationship('category', 'name', function (Builder $query, Transaction $record) {
                    $query->where('direction', $record->direction);
                })
                ->searchable()
        ]);
        $this->action(function (Transaction $record, array $data) {
            $mergeId = Str::uuid()->toString();
            $transaction = $record->replicate(['id']);
            $transaction->value = $data['value'];
            $transaction->category_id = $data['category_id'];
            $transaction->description = $data['description'];
            $transaction->merge_id = $mergeId;
            $transaction->save();
            $record->refresh();
            $record->update([
                'value' => $record->value - $data['value'],
                'merge_id' => $mergeId,
            ]);
        });

    }
}
