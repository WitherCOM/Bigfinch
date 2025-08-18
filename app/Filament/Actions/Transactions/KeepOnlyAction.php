<?php

namespace App\Filament\Actions\Transactions;

use Filament\Actions\Action;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use App\Models\Transaction;
use Filament\Forms\Components\TextInput;

class KeepOnlyAction extends Action
{
    public function setUp(): void
    {
        parent::setUp();
        $this->form([
            Tabs::make('Keep Only')
                ->tabs([
                    Tab::make('Value')
                        ->schema([
                            TextInput::make('value')
                                ->requiredWithout('percentage')
                        ]),
                    Tab::make('Percentage')
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
