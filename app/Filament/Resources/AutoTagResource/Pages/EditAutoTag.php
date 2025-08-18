<?php

namespace App\Filament\Resources\AutoTagResource\Pages;

use App\Filament\Resources\AutoTagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAutoTag extends EditRecord
{
    protected static string $resource = AutoTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
