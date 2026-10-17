<?php

namespace App\Filament\Widgets;

use App\Enums\Direction;
use App\Filament\Actions\Transactions\MergeBulkAction;
use App\Filament\Actions\Transactions\SplitAction;
use App\Filament\Forms\Components\ContextTagsInput;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CompleteTransactions extends TableWidget
{
    protected int | string | array $columnSpan = 2;
    protected static ?int $sort = 10;

    public function table(Table $table): Table
    {
        $user = Auth::user();
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
                TextColumn::make('date')
                    ->dateTime('Y-m-d H:i:s', $user->user_timezone),
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
                Action::make('book_transaction')
                    ->hiddenLabel(true)
                    ->icon('heroicon-o-book-open')
                    ->fillForm(fn (Transaction $record): array => [
                        'description' => $record->description,
                        'tags' => $record->tags,
                        'category_id' => $record->category_id,
                        'direction' => $record->direction,
                    ])
                    ->schema([
                        Select::make('category_id')
                            ->required()
                            ->label(__('Category'))
                            ->options(fn () => Category::query()->pluck('name', 'id')),
                        Select::make('direction')
                            ->required()
                            ->live()
                            ->options(Direction::class)
                            ->enum(Direction::class),
                        TextInput::make('description'),
                        ContextTagsInput::make('tags'),
                    ])
                    ->action(fn (Transaction $record, array $data) => $record->update($data)),
                DeleteAction::make()
                    ->icon('')
                    ->modal(false)
                    ->requiresConfirmation(false)
                    ->label(__('Exclude')),
                ActionGroup::make([
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
