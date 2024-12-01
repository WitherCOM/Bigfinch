<?php

namespace App\Filament\Resources;

use App\Enums\Direction;
use App\Filament\Resources\MerchantResource\Pages;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MerchantResource extends Resource
{
    protected static ?string $model = Merchant::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('expense_category_id')
                    ->preload()
                    ->relationship('expense_category', 'name', function (Builder $query) {
                        $query->where('direction', Direction::EXPENSE->value);
                    })
                    ->searchable(),
                Forms\Components\Select::make('income_category_id')
                    ->preload()
                    ->relationship('income_category', 'name', function (Builder $query) {
                        $query->where('direction', Direction::INCOME->value);
                    })
                    ->searchable(),
                Forms\Components\TagsInput::make('search_keys')
                    ->disabled()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\SelectColumn::make('income_category_id')
                    ->options(Category::where('direction', Direction::INCOME->value)->get()->pluck('name','id')),
                Tables\Columns\SelectColumn::make('expense_category_id')
                    ->options(Category::where('direction', Direction::EXPENSE->value)->get()->pluck('name','id')),
                Tables\Columns\TextColumn::make('key_factors')
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
                    Tables\Actions\BulkAction::make('merge')
                        ->form([
                            Forms\Components\TextInput::make('name')
                                ->datalist(fn(Collection $records) => $records->pluck('name'))
                                ->required()
                        ])
                        ->action(function (Collection $records, array $data) {
                            $merchant = Merchant::create([
                                'user_id' => Auth::id(),
                                'name' => $data['name'],
                                'search_keys' => $records->flatMap(fn($record) => $record->search_keys)
                            ]);
                            $records->each(function ($record) use ($merchant) {
                                $record->transactions()->update([
                                    'merchant_id' => $merchant->id
                                ]);
                                $record->delete();
                            });
                        })
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
            'index' => Pages\ListMerchants::route('/'),
            'create' => Pages\CreateMerchant::route('/create'),
            'edit' => Pages\EditMerchant::route('/{record}/edit'),
        ];
    }
}
