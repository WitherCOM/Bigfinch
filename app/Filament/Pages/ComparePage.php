<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Components\PrettyJsonField;
use App\Models\Transaction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class ComparePage extends Page
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.compare-page';

    protected static bool $shouldRegisterNavigation = false;

    public Collection $transactions;

    public function mount(): void {
        $ids = request()->query("ids");
        if (!is_array($ids)) {
            abort(400);
        }
        $this->transactions = Transaction::query()->whereIn('id', $ids)->with([
            'category',
            'currency',
            'integration'
        ])->get();
        if ($this->transactions->count() != count($ids)) {
            abort(404);
        }
    }

    protected function compareSchema(Schema $schema, Transaction $transaction): Schema
    {
         return $schema
             ->record($transaction)
             ->components([
                \Filament\Infolists\Components\TextEntry::make('description'),
                 \Filament\Infolists\Components\TextEntry::make('integration.institution_name')->label('Bank'),
                 \Filament\Infolists\Components\TextEntry::make('direction'),
                 \Filament\Infolists\Components\TextEntry::make('formatted_value'),
                 \Filament\Infolists\Components\TextEntry ::make('date')->date(),
                 \Filament\Infolists\Components\TextEntry::make('merchant'),
                 \Filament\Infolists\Components\TextEntry::make('currency.iso_code'),
                 \Filament\Infolists\Components\TextEntry::make('category.name')->label('Category'),
                 \Filament\Infolists\Components\TextEntry ::make('tags')->badge(),
                 PrettyJsonField::make('open_banking_transaction')
            ]);

    }
}
