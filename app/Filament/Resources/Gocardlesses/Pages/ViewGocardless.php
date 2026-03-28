<?php

namespace App\Filament\Resources\Gocardlesses\Pages;

use App\Filament\Resources\Gocardlesses\GocardlessResource;
use App\Filament\Resources\Gocardlesses\Widgets\AgreementsTable;
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
}
