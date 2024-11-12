<?php

namespace App\Filament\Resources\CategoryRuleResource\Pages;

use App\Filament\Resources\CategoryRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryRules extends ListRecords
{
    protected static string $resource = CategoryRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
