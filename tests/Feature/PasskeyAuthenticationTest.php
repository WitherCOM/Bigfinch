<?php

namespace Tests\Feature;

use App\Filament\Pages\ConfirmPassword;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Livewire\Livewire;
use Tests\TestCase;

class PasskeyAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_passkeys_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('passkeys'));
        $this->assertTrue(Schema::hasColumns('passkeys', [
            'user_id',
            'name',
            'credential_id',
            'credential',
            'last_used_at',
        ]));
    }

    public function test_user_is_a_passkey_user_without_passkeys_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(PasskeyUser::class, $user);
        $this->assertCount(0, $user->passkeys);
    }

    public function test_login_page_offers_passkey_sign_in(): void
    {
        $this->get(route('filament.app.auth.login'))
            ->assertOk()
            ->assertSee('passkey', false);
    }

    public function test_passkey_login_options_endpoint_is_available_to_guests(): void
    {
        $this->getJson(route('passkey.login-options'))->assertOk();
    }

    public function test_passkey_registration_requires_authentication(): void
    {
        $this->getJson(route('passkey.registration-options'))->assertUnauthorized();
    }

    public function test_profile_page_lists_passkey_management_for_authenticated_user(): void
    {
        Currency::factory()->create();

        $this->actingAs(User::factory()->create())
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('filament.app.auth.profile'))
            ->assertOk()
            ->assertSee('passkey', false);
    }

    public function test_password_confirm_route_points_to_confirmation_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('password.confirm'))
            ->assertRedirect(route('filament.app.pages.confirm-password'));
    }

    public function test_profile_page_redirects_to_password_confirmation_when_not_confirmed(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('filament.app.auth.profile'))
            ->assertRedirect(route('password.confirm'));
    }

    public function test_passkey_registration_requires_password_confirmation(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('passkey.registration-options'))
            ->assertStatus(423);
    }

    public function test_confirming_correct_password_unlocks_passkey_management(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Livewire::actingAs($user);

        Livewire::test(ConfirmPassword::class)
            ->fillForm(['password' => 'password'])
            ->call('confirm')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertNotNull(session('auth.password_confirmed_at'));
        $this->getJson(route('passkey.registration-options'))->assertOk();
    }

    public function test_confirming_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Livewire::actingAs($user);

        Livewire::test(ConfirmPassword::class)
            ->fillForm(['password' => 'wrong-password'])
            ->call('confirm')
            ->assertHasErrors(['data.password']);

        $this->assertNull(session('auth.password_confirmed_at'));
        $this->getJson(route('passkey.registration-options'))->assertStatus(423);
    }
}
