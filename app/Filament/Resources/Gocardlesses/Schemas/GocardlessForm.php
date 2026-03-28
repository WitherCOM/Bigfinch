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
                TextInput::make('secret_id'),
                TextInput::make('secret_key'),
                TextInput::make('max_connections')
                    ->integer()
                    ->default(50),
                Select::make('integrations')
                    ->multiple()
                    ->relationship('integrations'),
            ]);
    }
}
