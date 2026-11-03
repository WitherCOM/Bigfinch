<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('roles'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge(),
                TextColumn::make('created_at')
                    ->timezone($user->user_timezone)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('editRoles')
                    ->label('Edit roles')
                    ->icon('heroicon-o-shield-check')
                    ->authorize('update')
                    ->fillForm(fn (User $record): array => [
                        'roles' => $record->roles->pluck('id')->all(),
                    ])
                    ->schema([
                        Select::make('roles')
                            ->options(fn (): array => Role::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->multiple()
                            ->preload(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->syncRoles(Role::query()->whereIn('id', $data['roles'] ?? [])->get());
                    }),
            ]);
    }
}
