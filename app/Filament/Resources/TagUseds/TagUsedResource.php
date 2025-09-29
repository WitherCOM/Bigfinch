<?php

namespace App\Filament\Resources\TagUseds;

use App\Enums\NavGroup;
use Filament\Schemas\Schema;
use App\Filament\Resources\TagUseds\Pages\ListTagUsed;
use App\Filament\Resources\TagUsedResource\Pages;
use App\Models\Tag;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class TagUsedResource extends Resource
{
    protected static ?string $model = Tag::class;
    protected static string | UnitEnum | null $navigationGroup = NavGroup::STATISTICS;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('tag'),
                TextColumn::make('value')
                    ->sortable(),
                TextColumn::make('last_seen')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
            ])
            ->toolbarActions([
            ])
            ->defaultSort('last_seen','desc');
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
            'index' => ListTagUsed::route('/'),
        ];
    }
}
