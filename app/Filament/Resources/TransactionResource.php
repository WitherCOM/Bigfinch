<?php

namespace App\Filament\Resources;

use App\Enums\Direction;
use App\Filament\Actions\Transactions\KeepOnlyAction;
use App\Filament\Actions\Transactions\MergeBulkAction;
use App\Filament\Actions\Transactions\RunEngineBulkAction;
use App\Filament\Actions\Transactions\SplitAction;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Novadaemon\FilamentPrettyJson\Form\PrettyJsonField;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->required()
                    ->minValue(0)
                    ->numeric(),
                Forms\Components\Select::make('currency_id')
                    ->relationship('currency', 'iso_code')
                    ->preload()
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('direction')
                    ->required()
                    ->live()
                    ->options(Direction::class)
                    ->enum(Direction::class),
                Forms\Components\DateTimePicker::make('date')
                    ->required()
                    ->default(Carbon::now()),
                Forms\Components\Select::make('category_id')
                    ->preload()
                    ->relationship('category', 'name', function (Builder $query, Forms\Get $get) {
                        $query->where(function (Builder $query) {
                            $query->where('user_id', Auth::id())->orWhereNull('user_id');
                        })->where('direction', $get('direction'));
                    })
                    ->searchable(),
                Forms\Components\TagsInput::make('tags'),
                PrettyJsonField::make('open_banking_transaction')
            ]);
    }

    public static function table(Table $table): Table
    {
        $categories = Category::all();
        $monthSelect = [];
        for ($month = 1; $month <= Carbon::now()->month; $month++) {
            $monthSelect[$month] = Carbon::create(month: $month)->format('M');
        }

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('formatted_value')
                    ->color(function (Transaction $transaction) {
                        if ($transaction->direction == Direction::INTERNAL_TO || $transaction->direction == Direction::INTERNAL_FROM){
                            return 'info';
                        } else if ($transaction->direction == Direction::INVESTMENT) {
                            return 'warning';
                        } else if ($transaction->direction == Direction::EXPENSE) {
                            return 'danger';
                        } else if ($transaction->direction == Direction::INCOME) {
                            return 'success';
                        } else {
                            return 'neutral';
                        }
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\SelectColumn::make('category_id')
                    ->options(fn (Transaction $record) => $categories->pluck('name', 'id')),
                Tables\Columns\TextColumn::make('merchant')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Visibility')
                    ->trueLabel('With excluded')
                    ->falseLabel('Only excluded')
                    ->placeholder('Without excluded'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->preload()
                    ->relationship('category', 'name', function (Builder $query) {
                        $query->where(function (Builder $query) {
                            $query->where('user_id', Auth::id())->orWhereNull('user_id');
                        })->where('direction');
                    }),
                Tables\Filters\SelectFilter::make('Month')
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn (Builder $query, $month) => $query->whereMonth('date', $month)))
                    ->options($monthSelect),
                Tables\Filters\SelectFilter::make('direction')
                    ->options(Direction::class)
            ])
            ->recordClasses(fn(Transaction $transaction) => $transaction->trashed() ? 'opacity-50' : null)
            ->actions([
                KeepOnlyAction::make('keep_only')
                    ->authorize('update')
                    ->visible(fn(Transaction $record) => $record->direction === Direction::EXPENSE),
                SplitAction::make('split')
                    ->authorize('update')
                    ->visible(fn(Transaction $record) => $record->direction === Direction::EXPENSE),
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make()
                    ->modal(false)
                    ->requiresConfirmation(false)
                    ->label('Include'),
                Tables\Actions\DeleteAction::make()
                    ->icon('')
                    ->modal(false)
                    ->requiresConfirmation(false)
                    ->label('Exclude'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('')
                    ->visible()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->icon('')
                        ->label('Exclude'),
                    RunEngineBulkAction::make('run_engine'),
                    MergeBulkAction::make('merge'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible()
                ]),
            ])
            ->defaultSort('date', 'desc');
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
