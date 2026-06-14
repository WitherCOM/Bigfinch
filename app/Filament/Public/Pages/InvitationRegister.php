<?php

namespace App\Filament\Public\Pages;

use App\Models\Invitation;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function Laravel\Prompts\table;

class InvitationRegister extends Page implements HasForms
{
    use InteractsWithForms;
    protected string $view = 'filament.pages.invitation-register';
    protected static ?string $slug = 'register/{token}';
    public ?array $data = [];
    public ?Invitation $invitation = null;
    protected static bool $shouldRegisterNavigation = false;

    public static function isAuthorized(): bool
    {
        return true;
    }

    public function mount(string $token): void
    {
        $this->invitation = Invitation::where('token', $token)
            ->where('valid_until', '>', now())
            ->firstOr(fn () => throw new NotFoundHttpException());
        $this->form->fill([
            'email' => $this->invitation->email,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

               TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(table: 'users', column: 'email', ignoreRecord: false)
                    ->disabled(), // lock to invite email

               TextInput::make('password')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->confirmed(),

               TextInput::make('password_confirmation')
                    ->password()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function register(): void
    {
        $this->form->getState();
        $user = User::create([
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'password' => Hash::make($this->data['password']),
        ]);

        $this->invitation->delete();

        auth()->login($user);

        redirect()->route('filament.public.home');
    }
}
