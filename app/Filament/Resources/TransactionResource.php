<?php

namespace App\Filament\Resources;

use App\Enums\ActionType;
use App\Enums\Direction;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Filter;
use App\Models\Merchant;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\ForceDeleteAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['merchant','category'])->where('user_id', Auth::id());
    }

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
                    ->relationship('currency','iso_code')
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
                Forms\Components\Select::make('merchant_id')
                    ->relationship('merchant','name', function (Builder $query) {
                        $query->where('user_id', Auth::id());
                    })
                    ->searchable(),
                Forms\Components\Select::make('category_id')
                    ->preload()
                    ->relationship('category','name', function (Builder $query, Forms\Get $get) {
                        $query->where(function (Builder $query) {
                            $query->where('user_id', Auth::id())->orWhereNull('user_id');
                        })->where('direction',$get('direction'));
                    })
                    ->searchable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('formatted_value')
                    ->color(fn (Transaction $record) => $record->direction === Direction::EXPENSE ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->preload()
                    ->relationship('category','name', function (Builder $query) {
                        $query->where(function (Builder $query) {
                            $query->where('user_id', Auth::id())->orWhereNull('user_id');
                        })->where('direction');
                    }),
                Tables\Filters\SelectFilter::make('direction')
                    ->options(Direction::class)
                    ->default(Direction::EXPENSE->value)
            ])
            ->actions([
                Tables\Actions\Action::make('split')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->required()
                            ->minValue(0)
                            ->maxValue(fn (Transaction $record) => $record->value)
                            ->numeric(),
                        Forms\Components\TextInput::make('description')
                            ->afterStateHydrated(function (TextInput $component, Transaction $transaction) {
                                $component->state($transaction->description);
                            })
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->afterStateHydrated(function (Forms\Components\Select $component, Transaction $transaction) {
                                $component->state($transaction->category_id);
                            })
                            ->preload()
                            ->relationship('category','name', function (Builder $query, Transaction $record) {
                                $query->where(function (Builder $query) {
                                    $query->where('user_id', Auth::id())->orWhereNull('user_id');
                                })->where('direction',$record->direction);
                            })
                            ->searchable()
                    ])
                    ->visible(fn (Transaction $record) => $record->direction === Direction::EXPENSE)
                    ->action(function (Transaction $record, array $data) {
                        $record->value -= $data['value'];
                        $record->save();
                        $transcation = $record->replicate();
                        $transcation->value = $data['value'];
                        $transcation->category_id = $data['category_id'];
                        $transcation->description = $data['description'];
                        $transcation->save();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('recreate_merchants')
                        ->requiresConfirmation()
                        ->action(function (Collection $collection) {
                            foreach($collection as $record) {
                                if (!is_null($record->open_banking_transaction))
                                {
                                    $record->merchant_id = Merchant::getMerchant($record->open_banking_transaction, $record->user_id);
                                    $record->save();
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('auto_assign_category')
                        ->requiresConfirmation()
                        ->action(function (Collection $collection) {
                            $filters = Filter::all()->where('action',ActionType::CREATE_CATEGORY);
                            foreach($collection as $record) {
                                if (is_null($record->category_id))
                                {
                                    $record->category_id = $filters->filter(fn($filter) => $filter->check($record->toArray()))->sortByDesc('priority')->first()?->action_parameter;
                                    $record->save();
                                }
                            }
                        }),
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
