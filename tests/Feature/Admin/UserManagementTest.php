<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UserCreate;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_create_a_user_and_assign_a_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin);

        Livewire::test(UserCreate::class)
            ->set('name', 'New Reception Staff')
            ->set('email', 'newstaff@nesiha.test')
            ->set('password', 'password123')
            ->set('role', 'Reception')
            ->call('save')
            ->assertHasNoErrors();

        $user = User::where('email', 'newstaff@nesiha.test')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Reception'));
    }

    public function test_user_creation_fails_validation_with_duplicate_email(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        User::factory()->create(['email' => 'taken@nesiha.test']);

        $this->actingAs($admin);

        Livewire::test(UserCreate::class)
            ->set('name', 'Someone')
            ->set('email', 'taken@nesiha.test')
            ->set('password', 'password123')
            ->set('role', 'Reception')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($reception)
            ->get(route('admin.users.create'))
            ->assertForbidden();
    }
}
