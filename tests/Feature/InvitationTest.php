<?php

namespace Tests\Feature;

use App\Filament\Public\Pages\InvitationRegister;
use App\Filament\Resources\Invitations\Pages\CreateInvitation;
use App\Filament\Resources\Invitations\Pages\ListInvitations;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // LIST PAGE
    // ─────────────────────────────────────────────────────────────────────────

    public function test_list_invitations_page_is_accessible_for_normal_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ListInvitations::class)
            ->assertSuccessful();
    }

    public function test_list_invitations_page_is_accessible_for_admin_user(): void
    {
        $admin = User::factory()->create(['can_manage_settings' => true]);

        $this->actingAs($admin);

        Livewire::test(ListInvitations::class)
            ->assertSuccessful();
    }

    public function test_list_invitations_only_shows_own_invitations(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownInvitation = Invitation::factory()->create([
            'user_id' => $user->id,
            'email' => 'own@example.com',
        ]);

        $otherInvitation = Invitation::factory()->create([
            'user_id' => $otherUser->id,
            'email' => 'other@example.com',
        ]);

        $this->actingAs($user);

        Livewire::test(ListInvitations::class)
            ->assertCanSeeTableRecords([$ownInvitation])
            ->assertCanNotSeeTableRecords([$otherInvitation]);
    }

    public function test_admin_list_shows_only_admin_own_invitations(): void
    {
        $admin = User::factory()->create(['can_manage_settings' => true]);
        $otherUser = User::factory()->create();

        $adminInvitation = Invitation::factory()->create([
            'user_id' => $admin->id,
            'email' => 'admin-invited@example.com',
        ]);

        $otherInvitation = Invitation::factory()->create([
            'user_id' => $otherUser->id,
            'email' => 'other@example.com',
        ]);

        $this->actingAs($admin);

        Livewire::test(ListInvitations::class)
            ->assertCanSeeTableRecords([$adminInvitation])
            ->assertCanNotSeeTableRecords([$otherInvitation]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREATE PAGE — FORM & DB
    // ─────────────────────────────────────────────────────────────────────────

    public function test_create_invitation_page_is_accessible_for_normal_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateInvitation::class)
            ->assertSuccessful();
    }

    public function test_create_invitation_page_is_accessible_for_admin_user(): void
    {
        $admin = User::factory()->create(['can_manage_settings' => true]);

        $this->actingAs($admin);

        Livewire::test(CreateInvitation::class)
            ->assertSuccessful();
    }

    public function test_normal_user_can_create_invitation(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => 'newuser@example.com',
                'valid_until' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('invitations', [
            'email' => 'newuser@example.com',
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_user_can_create_invitation(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['can_manage_settings' => true]);

        $this->actingAs($admin);

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => 'admin-invited@example.com',
                'valid_until' => now()->addDays(5)->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('invitations', [
            'email' => 'admin-invited@example.com',
            'user_id' => $admin->id,
        ]);
    }

    public function test_create_invitation_validates_email_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => '',
                'valid_until' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    public function test_create_invitation_validates_duplicate_email_in_invitations_table(): void
    {
        $user = User::factory()->create();

        Invitation::factory()->create([
            'user_id' => $user->id,
            'email' => 'already-invited@example.com',
        ]);

        $this->actingAs($user);

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => 'already-invited@example.com',
                'valid_until' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    public function test_create_invitation_validates_email_not_already_registered_as_user(): void
    {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'existing@example.com']);

        $this->actingAs($user);

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => 'existing@example.com',
                'valid_until' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EMAIL SENDING
    // ─────────────────────────────────────────────────────────────────────────

    public function test_invitation_email_is_sent_when_normal_user_creates_invitation(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => 'newuser@example.com',
                'valid_until' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Mail::assertSent(InvitationMail::class, function (InvitationMail $mail) {
            return $mail->hasTo('newuser@example.com');
        });
    }

    public function test_invitation_email_is_sent_when_admin_user_creates_invitation(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['can_manage_settings' => true]);

        $this->actingAs($admin);

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => 'admin-invited@example.com',
                'valid_until' => now()->addDays(7)->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Mail::assertSent(InvitationMail::class, function (InvitationMail $mail) {
            return $mail->hasTo('admin-invited@example.com');
        });
    }

    public function test_invitation_email_is_sent_exactly_once_per_invitation(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => 'once@example.com',
                'valid_until' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ])
            ->call('create');

        Mail::assertSentCount(1);
    }

    public function test_invitation_email_contains_the_token_link(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateInvitation::class)
            ->fillForm([
                'email' => 'link-check@example.com',
                'valid_until' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ])
            ->call('create');

        $invitation = Invitation::where('email', 'link-check@example.com')->firstOrFail();

        Mail::assertSent(InvitationMail::class, function (InvitationMail $mail) use ($invitation) {
            return $mail->invitation->token === $invitation->token;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INVITATION REGISTER PAGE (PUBLIC PANEL) — HTTP RENDERING
    // ─────────────────────────────────────────────────────────────────────────

    public function test_invitation_register_page_renders_for_valid_token(): void
    {
        $invitation = Invitation::factory()->create();

        $this->get("/public/register/{$invitation->token}")
            ->assertSuccessful();
    }

    public function test_invitation_register_page_returns_404_for_invalid_token(): void
    {
        $this->get('/public/register/this-token-does-not-exist')
            ->assertNotFound();
    }

    public function test_invitation_register_page_returns_404_for_expired_token(): void
    {
        $invitation = Invitation::factory()->expired()->create();

        $this->get("/public/register/{$invitation->token}")
            ->assertNotFound();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INVITATION REGISTER PAGE — FORM INTERACTION
    // ─────────────────────────────────────────────────────────────────────────

    public function test_invitation_register_form_prefills_email_from_invitation(): void
    {
        $invitation = Invitation::factory()->create(['email' => 'prefill@example.com']);

        Livewire::test(InvitationRegister::class, ['token' => $invitation->token])
            ->assertFormSet(['email' => 'prefill@example.com']);
    }

    public function test_invitation_register_email_field_is_disabled(): void
    {
        $invitation = Invitation::factory()->create();

        Livewire::test(InvitationRegister::class, ['token' => $invitation->token])
            ->assertFormFieldDisabled('email');
    }

    public function test_successful_registration_creates_user_record(): void
    {
        $invitation = Invitation::factory()->create(['email' => 'register@example.com']);

        Livewire::test(InvitationRegister::class, ['token' => $invitation->token])
            ->fillForm([
                'name' => 'New User',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'register@example.com',
            'name' => 'New User',
        ]);
    }

    public function test_successful_registration_deletes_the_invitation(): void
    {
        $invitation = Invitation::factory()->create(['email' => 'consume@example.com']);

        Livewire::test(InvitationRegister::class, ['token' => $invitation->token])
            ->fillForm([
                'name' => 'Consume Test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
    }

    public function test_successful_registration_logs_in_the_new_user(): void
    {
        $invitation = Invitation::factory()->create(['email' => 'loggedin@example.com']);

        Livewire::test(InvitationRegister::class, ['token' => $invitation->token])
            ->fillForm([
                'name' => 'LoggedIn User',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $this->assertAuthenticated();

        $this->assertAuthenticatedAs(User::where('email', 'loggedin@example.com')->firstOrFail());
    }

    public function test_registration_fails_with_password_below_minimum_length(): void
    {
        $invitation = Invitation::factory()->create(['email' => 'short@example.com']);

        Livewire::test(InvitationRegister::class, ['token' => $invitation->token])
            ->fillForm([
                'name' => 'Short Pass',
                'password' => 'abc',
                'password_confirmation' => 'abc',
            ])
            ->call('register')
            ->assertHasFormErrors(['password']);
    }

    public function test_registration_fails_when_passwords_do_not_match(): void
    {
        $invitation = Invitation::factory()->create(['email' => 'mismatch@example.com']);

        Livewire::test(InvitationRegister::class, ['token' => $invitation->token])
            ->fillForm([
                'name' => 'Mismatch User',
                'password' => 'password123',
                'password_confirmation' => 'different456',
            ])
            ->call('register')
            ->assertHasFormErrors(['password']);
    }

    public function test_registration_fails_when_name_is_missing(): void
    {
        $invitation = Invitation::factory()->create(['email' => 'noname@example.com']);

        Livewire::test(InvitationRegister::class, ['token' => $invitation->token])
            ->fillForm([
                'name' => '',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->call('register')
            ->assertHasFormErrors(['name']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE POLICY
    // ─────────────────────────────────────────────────────────────────────────

    public function test_user_can_delete_own_invitation(): void
    {
        $user = User::factory()->create();

        $invitation = Invitation::factory()->create([
            'user_id' => $user->id,
            'email' => 'delete-me@example.com',
        ]);

        $this->actingAs($user);

        Livewire::test(ListInvitations::class)
            ->callTableAction('delete', $invitation)
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
    }

    public function test_admin_can_delete_own_invitation(): void
    {
        $admin = User::factory()->create(['can_manage_settings' => true]);

        $invitation = Invitation::factory()->create([
            'user_id' => $admin->id,
            'email' => 'admin-delete@example.com',
        ]);

        $this->actingAs($admin);

        Livewire::test(ListInvitations::class)
            ->callTableAction('delete', $invitation)
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
    }
}
