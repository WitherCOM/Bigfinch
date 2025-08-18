<?php

namespace App\Filament\Resources\Filters;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use App\Enums\Flag;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Filters\Pages\ListFilters;
use App\Filament\Resources\Filters\Pages\CreateFilter;
use App\Filament\Resources\Filters\Pages\EditFilter;
use App\Enums\FilterAction;
use App\Filament\Resources\FilterResource\Pages;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Filter;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class FilterResource extends Resource
{
    protected static ?string $model = Filter::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                Section::make('date_range')
                    ->schema([
                        DateTimePicker::make('from'),
                        DateTimePicker::make('to')
                    ]),
                Section::make('value_range')
                    ->schema([
                        TextInput::make('min_value')
                            ->numeric(),
                        TextInput::make('max_value')
                            ->numeric(),
                        Select::make('currency_id')
                            ->options(Currency::all()->mapWithKeys(fn(Currency $currency) => [$currency->id => $currency->iso_code]))
                            ->searchable(),
                    ]),
                TextInput::make('description'),
                TextInput::make('tag'),
                Select::make('flag')
                    ->options(Flag::class)
                    ->enum(Flag::class),
                Section::make('filter_action')
                    ->schema([
                        Select::make('action')
                            ->required()
                            ->live()
                            ->options(FilterAction::class)
                            ->enum(FilterAction::class),
                        Select::make('action_parameters.category_id')
                            ->visible(fn(Get $get) => $get('action') === FilterAction::CATEGORIZE->value)
                            ->required(fn(Get $get) => $get('action') === FilterAction::CATEGORIZE->value)
                            ->options(Currency::all()->mapWithKeys(fn(Category $category) => [$category->id => $category->name]))
                            ->searchable(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('filter_highlight'),
                TextColumn::make('action')
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
            'index' => ListFilters::route('/'),
            'create' => CreateFilter::route('/create'),
            'edit' => EditFilter::route('/{record}/edit'),
        ];
    }
}
