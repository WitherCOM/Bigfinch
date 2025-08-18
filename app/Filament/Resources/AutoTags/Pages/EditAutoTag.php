<?php

namespace App\Filament\Resources\AutoTags\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\AutoTags\AutoTagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAutoTag extends EditRecord
{
    protected static string $resource = AutoTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
