<?php

namespace App\Filament\Resources\Transactions;

use App\Enums\NavGroup;
use App\Filament\Actions\Transactions\SetOriginalAction;
use App\Filament\Forms\Components\PrettyJsonField;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Enums\Direction;
use App\Filament\Actions\Transactions\KeepOnlyAction;
use App\Filament\Actions\Transactions\MergeBulkAction;
use App\Filament\Actions\Transactions\RunEngineBulkAction;
use App\Filament\Actions\Transactions\SplitAction;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use UnitEnum;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static string | UnitEnum | null $navigationGroup = NavGroup::TRANSACTIONS;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select(['*',
                DB::raw('
                    CASE
                        WHEN transactions.category_id IS NULL THEN 0
                        ELSE 1
                    END AS is_category_null')]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('description')
                    ->required(),
                TextInput::make('value')
                    ->required()
                    ->minValue(0)
                    ->numeric(),
                Select::make('currency_id')
                    ->relationship('currency', 'iso_code')
                    ->preload()
                    ->required()
                    ->searchable(),
                Select::make('direction')
                    ->required()
                    ->live()
                    ->options(Direction::class)
                    ->enum(Direction::class),
                DateTimePicker::make('date')
                    ->required()
                    ->default(Carbon::now()),
                Select::make('category_id')
                    ->preload()
                    ->relationship('category', 'name', function (Builder $query, Get $get) {
                        $query->where(function (Builder $query) {
                            $query->where('user_id', Auth::id())->orWhereNull('user_id');
                        })->where('direction', $get('direction'));
                    })
                    ->searchable(),
                TagsInput::make('tags'),
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
                TextColumn::make('date'),
                SelectColumn::make('category_id')
                    ->rules([Rule::in($categories->pluck('id'))])
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
                TrashedFilter::make()
                    ->label('Visibility')
                    ->trueLabel('With excluded')
                    ->falseLabel('Only excluded')
                    ->placeholder('Without excluded'),
                SelectFilter::make('category_id')
                    ->preload()
                    ->relationship('category', 'name', function (Builder $query) {
                        $query->where(function (Builder $query) {
                            $query->where('user_id', Auth::id())->orWhereNull('user_id');
                        });
                    }),
                SelectFilter::make('Month')
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn (Builder $query, $month) => $query->whereMonth('date', $month)))
                    ->options($monthSelect),
                SelectFilter::make('direction')
                    ->options(Direction::class)
            ])
            ->recordClasses(fn(Transaction $transaction) => $transaction->trashed() ? 'opacity-50' : null)
            ->recordActions([
                EditAction::make(),
                RestoreAction::make()
                    ->modal(false)
                    ->requiresConfirmation(false)
                    ->label('Include'),
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
                    RunEngineBulkAction::make('run_engine'),
                    MergeBulkAction::make('merge'),
                    ForceDeleteBulkAction::make()
                        ->visible()
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->orderBy('is_category_null'))
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
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }
}
