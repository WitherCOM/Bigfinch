<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Enums\ActionType;
use App\Enums\Direction;
use App\Filament\Resources\TransactionResource;
use App\Jobs\SyncTransactions;
use App\Models\Filter;
use App\Models\Merchant;
use App\Models\Modules\CategorizeByMerchant;
use App\Models\RawMerchant;
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
        ];
    }
}
