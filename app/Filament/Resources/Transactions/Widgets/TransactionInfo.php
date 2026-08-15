<?php

namespace App\Filament\Resources\Transactions\Widgets;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class TransactionInfo extends Widget implements HasForms, HasInfolists
{
    use InteractsWithForms;
    use InteractsWithInfolists;

    public ?Model $record = null;

    protected string $view = 'filament.resources.transaction-resource.widgets.transaction-info';

    public function transactionInfoList(Schema $schema): Schema
    {
        return $schema
            ->record($this->record)
            ->components([
                Flex::make([
                    ImageEntry::make('integration.institution_logo')
                        ->label(''),
                    TextEntry::make('integration.name')
                        ->label('')
                        ->grow(),
                ]),
            ]);
    }
}
