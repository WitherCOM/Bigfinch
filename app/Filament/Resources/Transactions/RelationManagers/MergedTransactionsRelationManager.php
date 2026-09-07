<?php

namespace App\Filament\Resources\Transactions\RelationManagers;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class MergedTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'mergedTransactions';

    protected static ?string $relatedResource = TransactionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
            ]);
    }
}
