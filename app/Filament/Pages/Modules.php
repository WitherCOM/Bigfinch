<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Form;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use ReflectionClass;

class Modules extends Page
{
    public array $data;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.modules';

    public function mount()
    {
        $data = [];
        $modules = Auth::user()->modules;
        foreach (config('app.modules') as $module) {
            $data[$module] = $modules->contains($module);
        }
        $this->form->fill($data);
    }
    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->action(function () {
                    $user = Auth::user();
                    $user->modules = $this->form->getState();
                    $user->save();
                }),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema(
            collect(config('app.modules'))->map(function ($module) {
                $moduleInstance = (new ReflectionClass($module))->newInstance();
                return Checkbox::make($module)
                        ->label($moduleInstance->getName());
            })->toArray()
        )->statePath('data');
    }

}
