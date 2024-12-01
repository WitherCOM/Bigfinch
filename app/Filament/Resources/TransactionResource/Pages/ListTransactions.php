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
                    $filters = Filter::where('type', ActionType::CREATE_CATEGORY->value)->get();
                    foreach (Transaction::whereNull('category_id') as $record) {
                        $category_id = $filters->where('action',ActionType::CREATE_CATEGORY)->filter(fn($filter) => $filter->check($record->toArray()))->sortByDesc('priority')->first()?->action_parameter;
                        // use merchant category if no filter
                        if (is_null($category_id))
                        {
                            if ($record->direction === Direction::INCOME)
                            {
                                $category_id = $record->merchant->income_category_id;
                            }
                            else if ($record->direction === Direction::EXPENSE)
                            {
                                $category_id = $record->merchant->expense_category_id;
                            }
                        }
                        if (!is_null($category_id))
                        {
                            $record->update([
                                'category_id' => $category_id
                            ]);
                        }
                    }
                })
        ];
    }
}
