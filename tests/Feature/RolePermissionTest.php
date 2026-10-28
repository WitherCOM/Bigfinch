<?php

namespace Tests\Feature;

use App\Enums\NavGroup;
use App\Models\User;
use BezhanSalleh\FilamentShield\Resources\Roles\Pages\ListRoles;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_assigned_roles_and_permissions(): void
    {
        $permission = Permission::create(['name' => 'ViewAny:Role', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user = User::factory()->create();

        $this->assertFalse($user->can('ViewAny:Role'));

        $user->assignRole('editor');

        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->fresh()->can('ViewAny:Role'));
    }

    public function test_roles_resource_is_in_admin_navigation_group(): void
    {
        $this->assertEquals(NavGroup::ADMIN->value, RoleResource::getNavigationGroup()?->value ?? RoleResource::getNavigationGroup());
    }

    public function test_user_without_permission_cannot_open_roles_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(RoleResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_user_with_view_any_permission_can_list_roles(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create(['name' => 'ViewAny:Role', 'guard_name' => 'web']));
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $this->actingAs($user);
        Livewire::actingAs($user);

        Livewire::test(ListRoles::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$role]);
    }

    public function test_super_admin_can_open_roles_page_without_explicit_permissions(): void
    {
        Role::create(['name' => config('filament-shield.super_admin.name'), 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole(config('filament-shield.super_admin.name'));

        $this->actingAs($user)
            ->get(RoleResource::getUrl('index'))
            ->assertSuccessful();
    }
}
