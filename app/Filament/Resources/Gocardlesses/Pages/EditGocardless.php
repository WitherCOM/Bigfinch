<?php

namespace App\Filament\Resources\Gocardlesses\Pages;

use App\Filament\Resources\Gocardlesses\GocardlessResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGocardless extends EditRecord
{
    protected static string $resource = GocardlessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
