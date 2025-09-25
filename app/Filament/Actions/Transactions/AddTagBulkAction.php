<?php

namespace App\Filament\Actions\Transactions;

use App\Enums\Direction;
use App\Models\Transaction;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Collection;

class AddTagBulkAction extends BulkAction
{
    public function setUp(): void
    {
        parent::setUp();
        $this->schema([
            TextInput::make('tag')
                ->required()
                ->label('Tag'),

        ]);
        $this->action(function (Collection $records, array $data) {
            foreach ($records as $record) {
                $record->tags = array_unique(array_merge($record->tags,[$data['tag']]));
                $record->save();
            }
        });

    }
}
