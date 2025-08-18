<?php

namespace App\Filament\Resources\TagUseds\Pages;

use App\Filament\Resources\TagUseds\TagUsedResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTagUsed extends ListRecords
{
    protected static string $resource = TagUsedResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
