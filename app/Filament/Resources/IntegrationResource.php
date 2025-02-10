<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntegrationResource\Pages;
use App\Jobs\SyncTransactions;
use App\Models\Integration;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class IntegrationResource extends Resource
{
    protected static ?string $model = Integration::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name'),
                Forms\Components\Select::make('institution_id')
                    ->options(Integration::listBanks())
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('institution_logo')->label(''),
                Tables\Columns\TextColumn::make('name')
                    ->url(fn(Integration $record) => $record->can_accept ? $record->link : null),
                Tables\Columns\TextColumn::make('institution_name'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->color(fn(Integration $record) => $record->expired ? 'danger' : 'success'),
                Tables\Columns\CheckboxColumn::make('can_auto_sync'),
                Tables\Columns\TextColumn::make('last_synced_at')
                    ->color(fn(Integration $record) => $record->last_synced_at->lt(Carbon::today()) ? 'danger' : 'success')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('sync')
                    ->action(fn(Integration $record) => SyncTransactions::dispatch($record)),
                Tables\Actions\Action::make('renew')
                    ->action(function (Integration $record) {
                        $record->deleteRequisition();
                        $record->createRequisition();
                        $record->save();
                    }),
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('name')
                    ]),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIntegrations::route('/'),
            'create' => Pages\CreateIntegration::route('/create'),
        ];
    }
}
