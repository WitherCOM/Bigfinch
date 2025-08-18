<?php

namespace App\Filament\Resources\Filters\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Filters\FilterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFilter extends EditRecord
{
    protected static string $resource = FilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
