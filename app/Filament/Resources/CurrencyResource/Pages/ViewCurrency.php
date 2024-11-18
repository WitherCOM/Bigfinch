<?php

namespace App\Filament\Resources\CurrencyResource\Pages;

use App\Filament\Resources\CurrencyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCurrency extends ViewRecord
{
    protected static string $resource = CurrencyResource::class;

    public function getRelationManagers(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CurrencyResource\Widgets\CurrencyRateChart::make()
        ];
    }
}
