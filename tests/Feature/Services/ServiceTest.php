<?php

namespace Tests\Feature\Services;

use App\Livewire\Services\ServiceCreate;
use App\Livewire\Services\ServiceEdit;
use App\Models\Department;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_clinic_manager_can_create_a_service(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $department = Department::create(['name' => 'General Medicine']);

        $this->actingAs($manager);

        Livewire::test(ServiceCreate::class)
            ->set('department_id', (string) $department->id)
            ->set('name', 'Herbal Therapy Session')
            ->set('duration_minutes', '60')
            ->set('price', '500')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('services', ['name' => 'Herbal Therapy Session', 'department_id' => $department->id]);
    }

    public function test_service_creation_fails_validation_without_a_name(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $department = Department::create(['name' => 'General Medicine']);

        $this->actingAs($manager);

        Livewire::test(ServiceCreate::class)
            ->set('department_id', (string) $department->id)
            ->set('name', '')
            ->set('duration_minutes', '30')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_reception_cannot_manage_services(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('services.index'))
            ->assertForbidden();

        $this->actingAs($reception)
            ->get(route('services.create'))
            ->assertForbidden();
    }

    public function test_manager_can_mark_a_service_visible_on_the_public_website(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $department = Department::create(['name' => 'General Medicine']);
        $service = Service::create([
            'department_id' => $department->id, 'name' => 'Herbal Therapy Session', 'duration_minutes' => 60,
        ]);

        $this->actingAs($manager);

        Livewire::test(ServiceEdit::class, ['service' => $service])
            ->set('description', 'A guided herbal treatment session.')
            ->set('show_on_website', true)
            ->call('save')
            ->assertHasNoErrors();

        $service->refresh();
        $this->assertTrue($service->show_on_website);
        $this->assertSame('A guided herbal treatment session.', $service->description);
    }
}
