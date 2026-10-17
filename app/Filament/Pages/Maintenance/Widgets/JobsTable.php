<?php

namespace App\Filament\Pages\Maintenance\Widgets;

use App\Models\Maintenance\Job;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class JobsTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Jobs')
            ->query(fn (): Builder => Job::query())
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('queue')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payload')
                    ->label('Job')
                    ->formatStateUsing(fn (string $state): string => json_decode($state, true)['displayName'] ?? '—')
                    ->limit(60),
                TextColumn::make('attempts')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reserved_at')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('available_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                DeleteAction::make()
            ]);
    }
}
