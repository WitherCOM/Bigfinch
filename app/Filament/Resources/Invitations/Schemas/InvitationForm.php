<?php

namespace App\Filament\Resources\Invitations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->unique(table: 'invitations', column: 'email')
                    ->unique(table: 'users', column: 'email')
                    ->required(),
                DateTimePicker::make('valid_until')
                    ->minDate(now()->addDays(1))
                    ->maxDate(now()->addDays(30))
                    ->default(now()->addDays(3))
                    ->required(),
            ]);
    }
}
