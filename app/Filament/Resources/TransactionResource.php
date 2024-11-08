<?php

namespace App\Filament\Resources;

use App\Enums\Direction;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('currency_id')
                    ->relationship('currency','name')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('direction')
                    ->required()
                    ->options(Direction::class)
                    ->enum(Direction::class),
                Forms\Components\DateTimePicker::make('date')
                    ->required()
                    ->default(Carbon::now()),
                Forms\Components\Select::make('merchant_id')
                    ->relationship('merchant','name', function (Builder $query) {
                        $query->where('user_id', Auth::id());
                    })
                    ->searchable(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category','name', function (Builder $query) {
                        $query->where('user_id', Auth::id())->orWhereNull('user_id');
                    })
                    ->searchable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('formatted_value'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('category.name'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date','desc');
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
