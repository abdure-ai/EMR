<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\PermissionCreate;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_create_a_permission(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin);

        Livewire::test(PermissionCreate::class)
            ->set('name', 'reports.export')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('permissions', ['name' => 'reports.export']);
    }

    public function test_permission_creation_fails_validation_without_dot_notation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin);

        Livewire::test(PermissionCreate::class)
            ->set('name', 'inventoryview')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_permission_creation_fails_validation_with_duplicate_name(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin);

        Livewire::test(PermissionCreate::class)
            ->set('name', 'patients.view')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_non_admin_cannot_access_permission_management(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('admin.permissions.index'))
            ->assertForbidden();

        $this->actingAs($reception)
            ->get(route('admin.permissions.create'))
            ->assertForbidden();
    }
}
