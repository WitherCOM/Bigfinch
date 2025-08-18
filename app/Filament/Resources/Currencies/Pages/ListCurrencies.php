<?php

namespace App\Filament\Resources\Currencies\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Currencies\CurrencyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCurrencies extends ListRecords
{
    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
