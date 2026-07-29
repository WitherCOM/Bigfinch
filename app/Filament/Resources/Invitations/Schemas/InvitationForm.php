<?php

namespace App\Filament\Resources\Invitations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class InvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();

        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->unique(table: 'invitations', column: 'email')
                    ->unique(table: 'users', column: 'email')
                    ->required(),
                DateTimePicker::make('valid_until')
                    ->timezone($user->user_timezone)
                    ->minDate(now()->addDays(1))
                    ->maxDate(now()->addDays(30))
                    ->default(now()->addDays(3))
                    ->required(),
            ]);
    }
}
