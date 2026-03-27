<?php

namespace App\Filament\Resources\Gocardlesses\Pages;

use App\Filament\Resources\Gocardlesses\GocardlessResource;
use App\Filament\Resources\Gocardlesses\Widgets\AgreementsTable;
use App\Filament\Resources\Gocardlesses\Widgets\RequisitionsTable;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGocardless extends ViewRecord
{
    protected static string $resource = GocardlessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            RequisitionsTable::make(),
        ];
    }
}
