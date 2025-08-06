<?php

namespace App\Filament\Actions\Transactions;

use App\Models\Transaction;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;

class KeepOnlyAction extends Action
{
    public function setUp(): void
    {
        parent::setUp();
        $this->form([
            Tabs::make('Keep Only')
                ->tabs([
                    Tabs\Tab::make('Value')
                        ->schema([
                            TextInput::make('value')
                                ->requiredWithout('percentage')
                        ]),
                    Tabs\Tab::make('Percentage')
                        ->schema([
                            TextInput::make('percentage')
                                ->requiredWithout('value')
                                ->minValue(0)
                                ->maxValue(100)
                        ])
                ])
        ]);
        $this->action(function (Transaction $record, array $data) {
            if (!is_null($data['value']))
            {
                $record->value = $data['value'];
            } else if (!is_null($data['percentage'])) {
                $record->value = $record->value * $data['percentage'] / 100;
            }
            $record->save();
        });
    }
}
