<?php

namespace App\Filament\Actions\Transactions;

use App\Engine\OpenBankingEngine;
use Filament\Actions\Action;
use App\Models\Transaction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;

class SetOriginalAction extends Action
{
    public function setUp(): void
    {
        parent::setUp();
        $this->action(function (Transaction $record) {
            if (!is_null($record->open_banking_transaction))
            {
                $record->setRawAttributes(OpenBankingEngine::parse($record->open_banking_transaction));
                $record->save();
            }
        });
    }
}
