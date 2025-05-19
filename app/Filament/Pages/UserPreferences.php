<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Currency;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class UserPreferences extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    public ?array $data = [];
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-preferences';

    public function mount() {
        $user = Auth::user();
        $this->form->fill([
            'default_currency_id' => $user->default_currency_id,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
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
