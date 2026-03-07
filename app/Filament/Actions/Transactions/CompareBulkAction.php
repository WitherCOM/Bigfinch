<?php

namespace App\Filament\Actions\Transactions;

use App\Enums\Direction;
use App\Models\Transaction;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Collection;

class CompareBulkAction extends BulkAction
{
    public function setUp(): void
    {
        parent::setUp();
        $this->action(function (Collection $records, array $data) {
            $this->redirect(
                route("filament.admin.pages.compare-page",["ids" => $records->pluck('id')->toArray()])
            );
        });

    }
}
