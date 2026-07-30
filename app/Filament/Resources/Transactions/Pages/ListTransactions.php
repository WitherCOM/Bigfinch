<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Enums\Direction;
use Filament\Actions\CreateAction;
use App\Enums\ActionType;
use App\Filament\Actions\Transactions\LastFlagEngineAction;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Merchant;
use App\Models\Modules\CategorizeByMerchant;
use App\Models\RawMerchant;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')
                    ->whereNull('category_id')
                    ->whereIn('direction', [Direction::EXPENSE->value, Direction::INCOME->value])),
            'all' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
            'with_excluded' => Tab::make()
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            LastFlagEngineAction::make('run_flag_on_last'),
        ];
    }
}
