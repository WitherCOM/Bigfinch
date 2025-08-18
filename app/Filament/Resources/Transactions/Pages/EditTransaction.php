<?php

namespace App\Filament\Resources\Transactions\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Transactions\Widgets\TransactionInfo;
use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
          TransactionInfo::class
        ];
    }

}
