<?php

namespace App\Filament\Resources\Currencies\Pages;

use App\Filament\Resources\Currencies\Widgets\CurrencyRateChart;
use App\Filament\Resources\Currencies\CurrencyResource;
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
            CurrencyRateChart::make()
        ];
    }
}
