<?php

namespace App\Filament\Resources\AutoTagResource\Pages;

use App\Filament\Resources\AutoTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAutoTags extends ListRecords
{
    protected static string $resource = AutoTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
