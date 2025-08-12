<?php

namespace App\Filament\Resources\TagUsedResource\Pages;

use App\Filament\Resources\TagUsedResource;
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
