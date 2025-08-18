<?php

namespace App\Filament\Resources\Integrations;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Actions\Action;
use App\Jobs\RunFlagEngine;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Integrations\Pages\ListIntegrations;
use App\Filament\Resources\Integrations\Pages\CreateIntegration;
use App\Filament\Resources\IntegrationResource\Pages;
use App\Jobs\SyncTransactions;
use App\Models\Integration;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

class IntegrationResource extends Resource
{
    protected static ?string $model = Integration::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                Select::make('institution_id')
                    ->options(Integration::listBanks())
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('institution_logo')->label(''),
                TextColumn::make('name')
                    ->url(fn(Integration $record) => $record->can_accept ? $record->link : null),
                TextColumn::make('institution_name'),
                TextColumn::make('expires_at')
                    ->color(fn(Integration $record) => $record->expired ? 'danger' : 'success'),
                CheckboxColumn::make('can_auto_sync'),
                TextColumn::make('last_synced_at')
                    ->color(fn(Integration $record) => $record->last_synced_at->lt(Carbon::today()) ? 'danger' : 'success')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('sync')
                    ->action(fn(Integration $record) => Bus::chain([
                        new SyncTransactions($record),
                        new RunFlagEngine(Auth::user()->transactions()->where('date','>=', Carbon::now()->subDays(90))->get())
                    ])->dispatch()),
                Action::make('renew')
                    ->action(function (Integration $record) {
                        $record->deleteRequisition();
                        $record->createRequisition();
                        $record->save();
                    })
                    ->visible(fn(Integration $record): bool => $record->expired),
                EditAction::make()
                    ->schema([
                        TextInput::make('name')
                    ]),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListIntegrations::route('/'),
            'create' => CreateIntegration::route('/create'),
        ];
    }
}
