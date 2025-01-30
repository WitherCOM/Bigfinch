<?php

namespace App\Filament\Resources;

use App\Enums\Direction;
use App\Enums\Flag;
use App\Filament\Actions\RunEngineBulkAction;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Novadaemon\FilamentPrettyJson\PrettyJson;

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
                PrettyJson::make('open_banking_transaction')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('formatted_value')
                    ->color(function (Transaction $transaction) {
                        if ($transaction->flags->contains(Flag::INTERNAL_TRANSACTION)){
                            return 'info';
                        } else if ($transaction->flags->contains(Flag::INVESTMENT)) {
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
                Tables\Columns\TextColumn::make('category.name'),
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
                Tables\Filters\SelectFilter::make('direction')
                    ->options(Direction::class)
            ])
            ->recordClasses(fn(Transaction $transaction) => $transaction->trashed() ? 'opacity-50' : null)
            ->actions([
                Tables\Actions\Action::make('split')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->required()
                            ->minValue(0)
                            ->maxValue(fn(Transaction $record) => $record->value)
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
                            ->relationship('category', 'name', function (Builder $query, Transaction $record) {
                                $query->where('direction', $record->direction);
                            })
                            ->searchable()
                    ])
                    ->authorize('update')
                    ->visible(fn(Transaction $record) => $record->direction === Direction::EXPENSE)
                    ->action(function (Transaction $record, array $data) {
                        $transcation = $record->replicate(['id']);
                        $transcation->value = $data['value'];
                        $transcation->category_id = $data['category_id'];
                        $transcation->description = $data['description'];
                        $transcation->save();
                        $record->refresh();
                        $record->update([
                            'value' => $record->value - $data['value']
                        ]);
                    }),
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
