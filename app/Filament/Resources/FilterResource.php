<?php

namespace App\Filament\Resources;

use App\Enums\FilterAction;
use App\Filament\Resources\FilterResource\Pages;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Filter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class FilterResource extends Resource
{
    protected static ?string $model = Filter::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name'),
                Forms\Components\Section::make('date_range')
                    ->schema([
                        Forms\Components\DateTimePicker::make('from'),
                        Forms\Components\DateTimePicker::make('to')
                    ]),
                Forms\Components\Section::make('value_range')
                    ->schema([
                        Forms\Components\TextInput::make('min_value')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_value')
                            ->numeric(),
                        Forms\Components\Select::make('currency_id')
                            ->options(Currency::all()->mapWithKeys(fn(Currency $currency) => [$currency->id => $currency->iso_code]))
                            ->searchable(),
                    ]),
                Forms\Components\TextInput::make('description'),
                Forms\Components\TextInput::make('tag'),
                Forms\Components\Select::make('flag')
                    ->options(\App\Enums\Flag::class)
                    ->enum(\App\Enums\Flag::class),
                Forms\Components\Section::make('filter_action')
                    ->schema([
                        Forms\Components\Select::make('action')
                            ->required()
                            ->live()
                            ->options(\App\Enums\FilterAction::class)
                            ->enum(\App\Enums\FilterAction::class),
                        Forms\Components\Select::make('action_parameters.category_id')
                            ->visible(fn(Forms\Get $get) => $get('action') === FilterAction::CATEGORIZE->value)
                            ->required(fn(Forms\Get $get) => $get('action') === FilterAction::CATEGORIZE->value)
                            ->options(Currency::all()->mapWithKeys(fn(Category $category) => [$category->id => $category->name]))
                            ->searchable(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('filter_highlight'),
                Tables\Columns\TextColumn::make('action')
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
