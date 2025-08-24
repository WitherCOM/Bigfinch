<?php

namespace App\Filament\Resources\AutoTags;

use App\Enums\NavGroup;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AutoTags\Pages\ListAutoTags;
use App\Filament\Resources\AutoTags\Pages\CreateAutoTag;
use App\Filament\Resources\AutoTags\Pages\EditAutoTag;
use App\Filament\Resources\AutoTagResource\Pages;
use App\Filament\Resources\AutoTagResource\RelationManagers;
use App\Models\AutoTag;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AutoTagResource extends Resource
{
    protected static ?string $model = AutoTag::class;
    protected static string|null|\UnitEnum $navigationGroup = NavGroup::TRANSACTIONS;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tag'),
                DateTimePicker::make('from')
                    ->required(),
                DateTimePicker::make('to')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('tag'),
                TextColumn::make('from'),
                TextColumn::make('to')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('to', 'desc');
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
            'index' => ListAutoTags::route('/'),
            'create' => CreateAutoTag::route('/create'),
            'edit' => EditAutoTag::route('/{record}/edit'),
        ];
    }
}
