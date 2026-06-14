<?php

namespace App\Filament\Pages;
use App\Models\Currency;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function mount(): void
    {
        parent::mount();
        $this->form->fill([
            ...$this->data,
            'default_currency_id' => $this->getUser()->default_currency_id,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')->schema([
                    $this->getNameFormComponent(),
                    $this->getEmailFormComponent(),
                    Select::make('default_currency_id')
                        ->label(__('Default Currency'))
                        ->options(fn () => Currency::query()
                            ->orderBy('iso_code')
                            ->pluck('iso_code', 'id'))
                        ->searchable()
                        ->required(),
                ]),
                Section::make('Password')->schema([
                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                ])
            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->getUser();

        $settings = $user->settings ?? [];
        $settings['default_currency_id'] = $data['default_currency_id'];

        $user->settings = $settings;
        $user->save();

        unset($data['default_currency_id']);

        return $data;
    }
}
