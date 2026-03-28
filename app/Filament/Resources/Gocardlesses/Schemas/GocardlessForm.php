<?php

namespace App\Filament\Resources\Gocardlesses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GocardlessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('secret_id')
                    ->required(),
                TextInput::make('secret_key')
                    ->required(),
                TextInput::make('max_connections')
                    ->required()
                    ->integer()
                    ->default(50)
            ]);
    }
}
