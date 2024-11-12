<?php

namespace App\Filament\Resources;

use App\Enums\Direction;
use App\Filament\Resources\ExcludeRuleResource\Pages;
use App\Filament\Resources\ExcludeRuleResource\RelationManagers;
use App\Models\Currency;
use App\Models\Rule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExcludeRuleResource extends Resource
{
    protected static ?string $model = Rule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->exclude();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('description_lookup'),
                Forms\Components\Select::make('merchant_id_lookup')
                    ->searchable()
                    ->relationship('merchant_lookup','name'),
                Forms\Components\Select::make('currency_id_lookup')
                    ->searchable(['iso_code'])
                    ->getOptionLabelFromRecordUsing(fn(Currency $record) => $record->name)
                    ->relationship('currency_lookup'),
                Forms\Components\Select::make('category_id_lookup')
                    ->searchable()
                    ->relationship('category_lookup','name'),
                Forms\Components\Select::make('direction_lookup')
                    ->options(Direction::class)
                    ->enum(Direction::class),
                Forms\Components\TextInput::make('min_value_lookup')
                    ->numeric(),
                Forms\Components\TextInput::make('max_value_lookup')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('priority'),
                Tables\Columns\TextColumn::make('description_lookup'),
                Tables\Columns\TextColumn::make('merchant_lookup.name'),
                Tables\Columns\TextColumn::make('category_lookup.name'),
                Tables\Columns\TextColumn::make('currency_lookup.name'),
                Tables\Columns\TextColumn::make('direction_lookup'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListExcludeRules::route('/'),
            'create' => Pages\CreateExcludeRule::route('/create'),
            'edit' => Pages\EditExcludeRule::route('/{record}/edit'),
        ];
    }
}
