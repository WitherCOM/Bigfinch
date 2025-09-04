<?php

namespace App\Filament\Widgets;

use App\Enums\Direction;
use App\Filament\Actions\Transactions\KeepOnlyAction;
use App\Filament\Actions\Transactions\MergeBulkAction;
use App\Filament\Actions\Transactions\RunEngineBulkAction;
use App\Filament\Actions\Transactions\SetOriginalAction;
use App\Filament\Actions\Transactions\SplitAction;
use App\Filament\Tables\Columns\WorkingSelectColumn;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

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
                DeleteAction::make()
                    ->icon('')
                    ->modal(false)
                    ->requiresConfirmation(false)
                    ->label(__('Exclude')),
                ActionGroup::make([
                    SetOriginalAction::make('set_original')
                        ->visible(fn(Transaction $record) => !is_null($record->open_banking_transaction))
                        ->authorize('update'),
                    KeepOnlyAction::make('keep_only')
                        ->authorize('update')
                        ->visible(fn(Transaction $record) => $record->direction === Direction::EXPENSE),
                    SplitAction::make('split')
                        ->authorize('update')
                        ->visible(fn(Transaction $record) => $record->direction === Direction::EXPENSE),
                    ForceDeleteAction::make()
                        ->label(__('Permanently Delete'))
                        ->visible(),
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
