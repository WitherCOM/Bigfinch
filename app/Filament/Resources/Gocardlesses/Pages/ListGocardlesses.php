<?php

namespace App\Filament\Resources\Gocardlesses\Pages;

use App\Filament\Resources\Gocardlesses\GocardlessResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGocardlesses extends ListRecords
{
    protected static string $resource = GocardlessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
