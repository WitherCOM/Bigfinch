<?php

namespace App\Filament\Pages;

use App\Models\Invitation;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvitationRegister extends Page implements HasForms
{
    protected string $view = 'filament.pages.invitation-register';
    protected static ?string $route = '/register/{token}';

    private Invitation|null $invitation = null;

    public function mount(string $token): void
    {
        $this->invitation = Invitation::where('token', $token)
            ->whereNull('used_at')
            ->where('valid_until', '>', now())
            ->firstOr(fn () => throw new NotFoundHttpException());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

               TextInput::make('email')
                    ->email()
                    ->required()
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
        $data = $this->form->getState();

        if (User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'An account already exists for this email.',
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $this->invitation->delete();

        auth()->login($user);

        redirect()->route('filament.home');
    }
}
