<?php

namespace App\Filament\Resources\Currencies\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Currencies\Widgets\CurrencyRateChart;
use App\Filament\Resources\Currencies\CurrencyResource;
use App\Models\Currency;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Pages\EditRecord;

class EditCurrency extends EditRecord
{
    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CurrencyRateChart::class
        ];
    }
}
