<?php

namespace Tests\Feature\Services;

use App\Livewire\Departments\DepartmentCreate;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_clinic_manager_can_create_a_department(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        $this->actingAs($manager);

        Livewire::test(DepartmentCreate::class)
            ->set('name', 'Physiotherapy')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('departments', ['name' => 'Physiotherapy']);
    }

    public function test_department_names_must_be_unique(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        Department::create(['name' => 'General Medicine']);

        $this->actingAs($manager);

        Livewire::test(DepartmentCreate::class)
            ->set('name', 'General Medicine')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_reception_cannot_manage_departments(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('departments.index'))
            ->assertForbidden();
    }
}
