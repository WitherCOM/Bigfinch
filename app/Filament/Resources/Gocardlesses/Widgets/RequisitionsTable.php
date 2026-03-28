<?php

namespace App\Filament\Resources\Gocardlesses\Widgets;

use App\Models\Gocardless\GocardlessToken;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Model;

class RequisitionsTable extends TableWidget
{
    public ?Model $record = null;

    protected int | string | array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('RequisitionsRelationManager')
            ->records(fn () => $this->fetchRequisitions())
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
            ])
            ->recordActions([
                Action::make('delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (array $record) {
                        /** @var GocardlessToken $token */
                        $token = $this->record;
                        $token->deleteRequisition($record['id']);
                    }),
            ])
            ->paginated(false);
    }

    protected function fetchRequisitions(): array
    {
        /** @var GocardlessToken $token */
        $token = $this->record;
        return $token->listRequisitions();
    }
}
