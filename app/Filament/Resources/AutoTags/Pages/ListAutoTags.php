<?php

namespace App\Filament\Resources\AutoTags\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AutoTags\AutoTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAutoTags extends ListRecords
{
    protected static string $resource = AutoTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
