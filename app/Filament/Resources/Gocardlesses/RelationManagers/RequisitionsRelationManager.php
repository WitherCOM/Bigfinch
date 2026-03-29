<?php

namespace App\Filament\Resources\Gocardlesses\RelationManagers;

use App\Models\Gocardless\GocardlessToken;
use App\Models\Gocardless\RequisitionDto;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RequisitionsRelationManager extends RelationManager
{
    protected static string $relationship = 'requisitions';

    protected static ?string $title = 'Requisitions';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    protected function makeTable(): Table
    {
        return Table::make($this)
            ->heading(static::getTitle($this->getOwnerRecord(), $this->getPageClass()))
            ->records(fn () => collect($this->ownerRecord->getRequisitions())
                ->map(fn (RequisitionDto $dto) => $dto->toArray())
                ->all());
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->copyable()
                    ->limit(8)
                    ->tooltip(fn ($record) => $record['id']),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'LN' => 'success',
                        'CR' => 'info',
                        'EX' => 'danger',
                        'RJ' => 'danger',
                        'SA' => 'warning',
                        'GA' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('institution_id')
                    ->label('Institution'),
                TextColumn::make('created')
                    ->label('Created')
                    ->dateTime(),
                TextColumn::make('accounts_count')
                    ->label('Accounts')
                    ->numeric(),
                IconColumn::make('active')
                    ->boolean(),
                TextColumn::make('integration_name'),
                TextColumn::make('user_name'),
            ])
            ->recordActions([
                Action::make('delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (array $record) {
                        /** @var GocardlessToken $token */
                        $token = $this->ownerRecord;
                        $token->deleteRequisition($record['id']);
                    }),
            ])
            ->defaultSort('created', 'desc')
            ->paginated(false);
    }
}
