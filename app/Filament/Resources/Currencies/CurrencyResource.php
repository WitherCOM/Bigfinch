<?php

namespace App\Filament\Resources\Currencies;

use App\Enums\NavGroup;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Currencies\RelationManagers\RatesRelationManager;
use App\Filament\Resources\Currencies\Pages\ListCurrencies;
use App\Filament\Resources\Currencies\Pages\CreateCurrency;
use App\Filament\Resources\Currencies\Pages\ViewCurrency;
use App\Filament\Resources\Currencies\Pages\EditCurrency;
use App\Enums\CurrencyPosition;
use App\Filament\Resources\CurrencyResource\Pages;
use App\Filament\Resources\CurrencyResource\RelationManagers;
use App\Models\Currency;
use App\Rules\CurrencyCodeRule;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static string | \UnitEnum | null $navigationGroup = NavGroup::SETTINGS;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('position')
                    ->required()
                    ->options(CurrencyPosition::class)
                    ->enum(CurrencyPosition::class),
                TextInput::make('symbol')
                    ->required(),
                TextInput::make('iso_code')
                    ->rule(new CurrencyCodeRule())
                    ->required()
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('iso_code')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('rate')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
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
            RatesRelationManager::make()
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'view' => ViewCurrency::route('/{record}'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }
}
