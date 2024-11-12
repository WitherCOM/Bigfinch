<?php

namespace App\Filament\Resources\ExcludeRuleResource\Pages;

use App\Filament\Resources\ExcludeRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExcludeRules extends ListRecords
{
    protected static string $resource = ExcludeRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
