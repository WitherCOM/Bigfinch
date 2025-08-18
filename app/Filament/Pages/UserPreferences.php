<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use App\Models\Category;
use App\Models\Currency;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class UserPreferences extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    public ?array $data = [];
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.user-preferences';

    public function mount() {
        $user = Auth::user();
        $this->form->fill([
            'default_currency_id' => $user->default_currency_id,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
           Select::make('default_currency_id')
               ->label(__('Default Currency'))
               ->options(Currency::all()->pluck('iso_code', 'id'))
        ])->statePath('data');
    }

    public function save() {
        $user = Auth::user();
        $user->settings = $this->form->getState();
        $user->save();
    }
}
