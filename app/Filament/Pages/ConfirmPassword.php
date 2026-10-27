<?php

namespace App\Filament\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Concerns\HasMaxWidth;
use Filament\Pages\Concerns\HasTopbar;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Target of the `password.confirm` middleware, which guards passkey creation and deletion.
 *
 * @property-read Schema $form
 */
class ConfirmPassword extends Page
{
    use HasMaxWidth;
    use HasTopbar;
    use WithRateLimiting;

    protected string $view = 'filament-panels::pages.simple';

    protected static string $layout = 'filament-panels::components.layout.simple';

    protected static ?string $slug = 'confirm-password';

    protected static bool $shouldRegisterNavigation = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * @return array<string, mixed>
     */
    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => $this->hasTopbar(),
            'maxContentWidth' => $maxContentWidth = $this->getMaxWidth() ?? $this->getMaxContentWidth(),
            'maxWidth' => $maxContentWidth,
        ];
    }

    public function hasLogo(): bool
    {
        return true;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function confirm(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'data.password' => __('filament-panels::auth/pages/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => $exception->minutesUntilAvailable,
                ]),
            ]);
        }

        $password = $this->form->getState()['password'];

        if (! Hash::check($password, Filament::auth()->user()->getAuthPassword())) {
            throw ValidationException::withMessages([
                'data.password' => __('auth.password'),
            ]);
        }

        session()->put('auth.password_confirmed_at', time());

        $this->redirectIntended(Filament::getUrl(), navigate: false);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->autofocus(),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('Confirm password');
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('Confirm password');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Please confirm your password before managing passkeys.');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('confirm')
                    ->footer([
                        Actions::make([
                            Action::make('confirm')
                                ->label(__('Confirm'))
                                ->submit('confirm'),
                        ])->fullWidth(),
                    ]),
            ]);
    }
}
