<?php

namespace App\Filament\Resources\Integrations;

use App\Enums\NavGroup;
use App\Filament\Resources\Integrations\Pages\CreateIntegration;
use App\Filament\Resources\Integrations\Pages\ListIntegrations;
use App\Jobs\RunFlagEngine;
use App\Jobs\SyncTransactions;
use App\Models\Gocardless\GocardlessToken;
use App\Models\Integration;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

class IntegrationResource extends Resource
{
    protected static ?string $model = Integration::class;
    protected static string|null|\UnitEnum $navigationGroup = NavGroup::TRANSACTIONS;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                Select::make('institution_id')
                    ->options(fn () => Integration::listBanks(GocardlessToken::firstOrFail()))
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
                ActionGroup::make([
                    Action::make('force_renew')
                        ->action(function (Integration $record) {
                            $record->deleteRequisition();
                            $record->createRequisition();
                            $record->save();
                        }),
                    DeleteAction::make()
                ]),
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
