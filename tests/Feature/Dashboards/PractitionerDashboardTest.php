<?php

namespace Tests\Feature\Dashboards;

use App\Livewire\Dashboards\PractitionerDashboard;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PractitionerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makePatient(string $phone = '+251900000001'): Patient
    {
        return Patient::create([
            'first_name' => 'Sara', 'last_name' => 'Ibrahim', 'sex' => 'female',
            'age' => 30, 'phone' => $phone,
        ]);
    }

    protected function makePractitioner(): User
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');

        return $practitioner;
    }

    public function test_practitioner_can_start_and_complete_their_own_queue_entry(): void
    {
        $practitioner = $this->makePractitioner();
        $patient = $this->makePatient();

        $entry = QueueEntry::create([
            'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id,
        ]);

        $this->actingAs($practitioner);

        Livewire::test(PractitionerDashboard::class)
            ->call('start', $entry->id)
            ->assertHasNoErrors();

        $this->assertSame('with_practitioner', $entry->fresh()->status);

        Livewire::test(PractitionerDashboard::class)
            ->call('complete', $entry->id);

        $this->assertSame('completed', $entry->fresh()->status);
    }

    public function test_practitioner_can_cancel_their_own_queue_entry(): void
    {
        $practitioner = $this->makePractitioner();
        $patient = $this->makePatient();

        $entry = QueueEntry::create([
            'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id,
        ]);

        $this->actingAs($practitioner);

        Livewire::test(PractitionerDashboard::class)
            ->call('cancel', $entry->id);

        $this->assertSame('cancelled', $entry->fresh()->status);
    }

    public function test_practitioner_cannot_manage_another_practitioners_queue_entry(): void
    {
        $practitionerA = $this->makePractitioner();
        $practitionerB = $this->makePractitioner();
        $patient = $this->makePatient();

        $entry = QueueEntry::create([
            'patient_id' => $patient->id,
            'practitioner_id' => $practitionerB->id,
        ]);

        $this->actingAs($practitionerA);

        Livewire::test(PractitionerDashboard::class)
            ->call('start', $entry->id)
            ->assertForbidden();

        $this->assertSame('waiting', $entry->fresh()->status);
    }
}
