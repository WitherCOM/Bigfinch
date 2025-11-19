<?php

namespace App\Filament\Widgets;

use App\Enums\Direction;
use App\Filament\Actions\Transactions\KeepOnlyAction;
use App\Filament\Actions\Transactions\MergeBulkAction;
use App\Filament\Actions\Transactions\SplitAction;
use App\Filament\Tables\Columns\WorkingSelectColumn;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CompleteTransactions extends TableWidget
{
    protected int | string | array $columnSpan = 2;
    protected static ?int $sort = 10;

    public function table(Table $table): Table
    {
        $categories = Category::all();

        return $table
            ->query(fn (): Builder => Transaction::query()
                ->whereIn('direction', [
                    Direction::EXPENSE->value,
                    Direction::INCOME->value
                ])
                ->where('date','>', Carbon::now()->subMonths(2))
                ->whereNull('category_id')
            )
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date'),
                WorkingSelectColumn::make('category_id')
                    ->options(fn (Transaction $record) => $categories->pluck('name', 'id')),
                TextColumn::make('formatted_value')
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
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('merchant')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->schema([
                        TextInput::make('description'),
                        TagsInput::make('tags'),
                        Select::make('direction')
                            ->required()
                            ->live()
                            ->options(Direction::class)
                            ->enum(Direction::class)
                    ]),
                DeleteAction::make()
                    ->icon('')
                    ->modal(false)
                    ->requiresConfirmation(false)
                    ->label(__('Exclude')),
                ActionGroup::make([
                    KeepOnlyAction::make('keep_only')
                        ->authorize('update')
                        ->visible(fn(Transaction $record) => $record->direction === Direction::EXPENSE),
                    SplitAction::make('split')
                        ->authorize('update')
                        ->visible(fn(Transaction $record) => $record->direction === Direction::EXPENSE)
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->icon('')
                        ->label('Exclude'),
                    MergeBulkAction::make('merge'),
                    ForceDeleteBulkAction::make()
                        ->visible()
                ]),
            ]);
    }
}
