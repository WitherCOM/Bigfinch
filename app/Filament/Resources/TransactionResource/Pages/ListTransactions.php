<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Enums\ActionType;
use App\Enums\Direction;
use App\Filament\Resources\TransactionResource;
use App\Jobs\SyncTransactions;
use App\Models\Filter;
use App\Models\Merchant;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('manual_merchant_assign')
                ->action(function(){
                    foreach (Transaction::withTrashed()->get() as $record) {
                        if (!is_null($record->open_banking_transaction)) {
                            $record->merchant_id = Merchant::getMerchant($record->open_banking_transaction, $record->user_id);
                            $record->save();
                        }
                    }
                }),
            Actions\Action::make('manual_category_assign')
                ->action(function() {
                    Filter::category();
                })
        ];
    }
}
