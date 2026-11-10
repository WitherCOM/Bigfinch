<?php

namespace App\Filament\Resources\Transactions\RelationManagers;

use App\Models\File;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';

    protected static ?string $title = 'Bills';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label('File')
                    ->disk(config('filesystems.default'))
                    ->visibility('private')
                    ->directory('bills')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(10240)
                    ->storeFileNamesIn('name')
                    ->openable()
                    ->downloadable()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('mime_type')
                    ->badge(),
                TextColumn::make('size')
                    ->numeric()
                    ->suffix(' B')
                    ->sortable(),
                TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label('Transactions'),
                TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i:s', Auth::user()->user_timezone)
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Upload')
                    ->visible(fn () => $this->getOwnerRecord()->files()->doesntExist())
                    ->mutateDataUsing(function (array $data): array {
                        $data['mime_type'] = Storage::mimeType($data['path']);
                        $data['size'] = Storage::size($data['path']);

                        $file = new File($data);
                        $file->convertToGrayscale();
                        $data['size'] = $file->size;

                        return $data;
                    }),
                AttachAction::make()
                    ->visible(fn () => $this->getOwnerRecord()->files()->doesntExist())
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->where('files.user_id', Auth::id())),
            ])
            ->recordActions([
                Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->authorize('view')
                    ->visible(fn (File $record) => str_starts_with($record->mime_type, 'image/'))
                    ->modalHeading(fn (File $record) => $record->name)
                    ->modalContent(fn (File $record) => view('filament.resources.transaction-resource.relation-managers.file-preview', [
                        'file' => $record,
                        'url' => Storage::temporaryUrl($record->path, now()->addMinutes(5)),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('Close')),
                Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->authorize('view')
                    ->action(fn (File $record) => Storage::download($record->path, $record->name)),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
