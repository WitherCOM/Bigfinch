<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class TransactionInfo extends Widget implements HasForms, HasInfolists
{
    use InteractsWithInfolists;
    use InteractsWithForms;

    public ?Model $record = null;

    protected static string $view = 'filament.resources.transaction-resource.widgets.transaction-info';

    public function transactionInfoList(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Split::make([
                    TextEntry::make('Integration'),
                    ImageEntry::make('integration.institution_logo')
                        ->label(''),
                    TextEntry::make('integration.name')
                        ->label('')
                        ->grow()
                ])
            ]);
    }

}
