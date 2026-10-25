<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Passkeys\Contracts\PasskeyUser;
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
            ->get(route('filament.app.auth.profile'))
            ->assertOk()
            ->assertSee('passkey', false);
    }
}
