<?php

namespace App\Filament\Pages\Maintenance\Widgets;

use App\Models\Maintenance\Migration;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class MigrationsTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Migrations')
            ->query(fn (): Builder => Migration::query())
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('migration')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('batch')
                    ->numeric()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('run')
                    ->label('Run migrations')
                    ->icon('heroicon-o-play')
                    ->requiresConfirmation()
                    ->modalDescription('This will run all pending database migrations.')
                    ->action(function (): void {
                        try {
                            $exitCode = Artisan::call('migrate', ['--force' => true]);
                            $output = trim(Artisan::output());

                            if ($exitCode !== 0) {
                                Notification::make()
                                    ->title('Migration failed')
                                    ->body($output ?: 'The migrate command returned a non-zero exit code.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Migrations ran successfully')
                                ->body($output ?: 'Nothing to migrate.')
                                ->success()
                                ->send();

                            $this->resetTable();
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->title('Migration failed')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}
