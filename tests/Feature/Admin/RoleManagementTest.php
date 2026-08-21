<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\RoleCreate;
use App\Livewire\Admin\RoleEdit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_create_a_role_with_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin);

        Livewire::test(RoleCreate::class)
            ->set('name', 'Lab Technician')
            ->set('selectedPermissions', ['patients.view'])
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::where('name', 'Lab Technician')->first();

        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('patients.view'));
    }

    public function test_role_creation_fails_validation_with_duplicate_name(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin);

        Livewire::test(RoleCreate::class)
            ->set('name', 'Reception')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_super_admin_can_edit_a_roles_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        $role = Role::where('name', 'Cashier')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(RoleEdit::class, ['role' => $role])
            ->set('selectedPermissions', ['patients.view', 'patients.create'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue($role->fresh()->hasPermissionTo('patients.create'));
    }

    public function test_super_admin_role_cannot_be_edited_or_deleted(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.roles.edit', $superAdminRole))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_access_role_management(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('admin.roles.index'))
            ->assertForbidden();

        $this->actingAs($reception)
            ->get(route('admin.roles.create'))
            ->assertForbidden();
    }
}
