<?php

namespace App\Filament\Resources\ExcludeRuleResource\Pages;

use App\Filament\Resources\ExcludeRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExcludeRule extends EditRecord
{
    protected static string $resource = ExcludeRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
