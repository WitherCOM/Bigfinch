<?php

namespace App\Filament\Resources;

use App\Enums\ActionType;
use App\Enums\Direction;
use App\Filament\Resources\FilterResource\Pages;
use App\Models\Category;
use App\Models\Filter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class FilterResource extends Resource
{
    protected static ?string $model = Filter::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
