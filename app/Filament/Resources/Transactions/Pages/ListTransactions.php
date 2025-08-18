<?php

namespace App\Filament\Resources\Transactions\Pages;

use Filament\Actions\CreateAction;
use App\Enums\ActionType;
use App\Filament\Actions\Transactions\LastFlagEngineAction;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Merchant;
use App\Models\Modules\CategorizeByMerchant;
use App\Models\RawMerchant;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            LastFlagEngineAction::make('run_flag_on_last'),
        ];
    }
}
