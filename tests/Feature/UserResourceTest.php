<?php

namespace Tests\Feature;

use App\Filament\Pages\Maintenance;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    private function managerUser(): User
    {
        $manager = User::factory()->create();
        $manager->givePermissionTo(
            Permission::create(['name' => 'ViewAny:User', 'guard_name' => 'web']),
            Permission::create(['name' => 'Update:User', 'guard_name' => 'web']),
        );

        return $manager;
    }

    public function test_user_without_permission_cannot_open_users_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_list_users(): void
    {
        $manager = $this->managerUser();
        $other = User::factory()->create();
        Livewire::actingAs($manager);

        Livewire::test(ListUsers::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$manager, $other]);
    }

    public function test_users_resource_has_no_create_or_edit_pages(): void
    {
        $this->assertSame(['index'], array_keys(UserResource::getPages()));
    }

    public function test_roles_can_be_changed_and_other_data_is_untouched(): void
    {
        $manager = $this->managerUser();
        $target = User::factory()->create(['name' => 'Target', 'email' => 'target@example.com']);
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Livewire::actingAs($manager);

        Livewire::test(ListUsers::class)
            ->callTableAction('editRoles', $target, ['roles' => [$role->id]])
            ->assertHasNoTableActionErrors();

        $target->refresh();
        $this->assertTrue($target->hasRole('editor'));
        $this->assertSame('Target', $target->name);
        $this->assertSame('target@example.com', $target->email);

        Livewire::test(ListUsers::class)
            ->callTableAction('editRoles', $target, ['roles' => []]);

        $this->assertFalse($target->fresh()->hasRole('editor'));
    }

    public function test_user_without_update_permission_cannot_edit_roles(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo(Permission::create(['name' => 'ViewAny:User', 'guard_name' => 'web']));
        $target = User::factory()->create();
        Livewire::actingAs($viewer);

        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('editRoles', $target);
    }

    public function test_settings_resources_require_permissions_instead_of_flag(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->can('viewAny', Currency::class));
        $this->assertFalse(Maintenance::canAccess());

        $user->givePermissionTo(Permission::create(['name' => 'ViewAny:Currency', 'guard_name' => 'web']));
        $this->assertTrue($user->fresh()->can('viewAny', Currency::class));
    }

    public function test_super_admin_can_manage_settings(): void
    {
        $role = Role::create(['name' => config('filament-shield.super_admin.name'), 'guard_name' => 'web']);
        $this->actingAs(User::factory()->create()->assignRole($role));

        $this->assertTrue(Maintenance::canAccess());
        $this->assertTrue(auth()->user()->can('viewAny', Currency::class));
    }
}
