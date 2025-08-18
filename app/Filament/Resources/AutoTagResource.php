<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AutoTagResource\Pages;
use App\Filament\Resources\AutoTagResource\RelationManagers;
use App\Models\AutoTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AutoTagResource extends Resource
{
    protected static ?string $model = AutoTag::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('tag'),
                Forms\Components\DateTimePicker::make('from')
                    ->required(),
                Forms\Components\DateTimePicker::make('to')
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
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAutoTags::route('/'),
            'create' => Pages\CreateAutoTag::route('/create'),
            'edit' => Pages\EditAutoTag::route('/{record}/edit'),
        ];
    }
}
