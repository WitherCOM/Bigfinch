<?php

namespace App\Filament\Resources;

use App\Enums\Direction;
use App\Filament\Resources\FilterResource\Pages;
use App\Filament\Resources\FilterResource\RelationManagers;
use App\Models\Filter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FilterResource extends Resource
{
    protected static ?string $model = Filter::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description'),
                Forms\Components\TextInput::make('merchant'),
                Forms\Components\TextInput::make('min_value')
                    ->numeric(),
                Forms\Components\TextInput::make('max_value')
                    ->numeric(),
                Forms\Components\Select::make('direction')
                    ->options(Direction::class)
                    ->enum(Direction::class)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('priority'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('merchant'),
                Tables\Columns\TextColumn::make('min_value'),
                Tables\Columns\TextColumn::make('max_value'),
                Tables\Columns\TextColumn::make('direction')
                    ->badge(),
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
            'index' => Pages\ListFilters::route('/'),
            'create' => Pages\CreateFilter::route('/create'),
            'edit' => Pages\EditFilter::route('/{record}/edit'),
        ];
    }
}
