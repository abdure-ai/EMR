<?php

namespace Tests\Feature\AuditLog;

use App\Livewire\AuditLog\AuditLogIndex;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makeManager(): User
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        return $manager;
    }

    public function test_non_manager_cannot_view_the_audit_log(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('audit-log.index'))
            ->assertForbidden();
    }

    public function test_manager_can_view_the_audit_log(): void
    {
        $this->actingAs($this->makeManager())
            ->get(route('audit-log.index'))
            ->assertOk();
    }

    public function test_creating_a_record_is_logged_and_visible(): void
    {
        $manager = $this->makeManager();

        $this->actingAs($manager);

        $department = Department::create(['name' => 'Cardiology']);

        Livewire::test(AuditLogIndex::class)
            ->assertSee('Department')
            ->assertSee('created')
            ->assertSee('#'.$department->id);
    }

    public function test_updating_a_record_shows_a_before_after_diff(): void
    {
        $manager = $this->makeManager();
        $this->actingAs($manager);

        $patient = Patient::create([
            'first_name' => 'Sara', 'last_name' => 'Ibrahim', 'sex' => 'female',
            'age' => 30, 'phone' => '+251900000001',
        ]);
        $patient->update(['phone' => '+251900000099']);

        $component = Livewire::test(AuditLogIndex::class);

        $component->assertSee('updated');
        $this->assertStringContainsString('+251900000001', $component->html());
        $this->assertStringContainsString('+251900000099', $component->html());
    }

    public function test_filters_by_action(): void
    {
        $manager = $this->makeManager();
        $this->actingAs($manager);

        $department = Department::create(['name' => 'Cardiology']);
        $department->update(['name' => 'Neurology']);

        Livewire::test(AuditLogIndex::class)
            ->set('action', 'created')
            ->assertSee('1 entry')
            ->assertSee('created');
    }

    public function test_search_matches_numeric_entity_id(): void
    {
        $manager = $this->makeManager();
        $this->actingAs($manager);

        $department = Department::create(['name' => 'Cardiology']);

        Livewire::test(AuditLogIndex::class)
            ->set('search', (string) $department->id)
            ->assertSee('Cardiology')
            ->assertSee('Department');
    }
}
