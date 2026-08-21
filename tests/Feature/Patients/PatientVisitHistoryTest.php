<?php

namespace Tests\Feature\Patients;

use App\Livewire\Patients\PatientShow;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientVisitHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makePatient(): Patient
    {
        return Patient::create([
            'first_name' => 'Sara',
            'last_name' => 'Ibrahim',
            'sex' => 'female',
            'age' => 30,
            'phone' => '+251900000001',
        ]);
    }

    protected function makePractitioner(): User
    {
        $practitioner = User::factory()->create(['name' => 'Dr Bekele']);
        $practitioner->assignRole('Practitioner');

        return $practitioner;
    }

    public function test_patient_show_lists_encounters_with_the_treating_practitioner(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();

        // Creating the queue entry auto-creates its draft encounter.
        QueueEntry::create([
            'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id,
            'status' => 'completed',
            'check_in_time' => now()->subDays(10),
            'completed_at' => now()->subDays(10),
        ]);

        $this->actingAs($manager);

        Livewire::test(PatientShow::class, ['patient' => $patient])
            ->assertSee('Encounters')
            ->assertSee('Dr Bekele')
            ->assertSee('Draft');
    }

    public function test_patient_show_reports_no_encounters_for_a_first_time_patient(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $patient = $this->makePatient();

        $this->actingAs($manager);

        Livewire::test(PatientShow::class, ['patient' => $patient])
            ->assertSee('No encounters yet.');
    }

    public function test_view_icon_hidden_from_roles_without_medical_view(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();

        $queueEntry = QueueEntry::create([
            'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id,
        ]);

        $this->actingAs($reception);

        Livewire::test(PatientShow::class, ['patient' => $patient])
            ->assertSee('Encounters')
            ->assertDontSee(route('encounters.show', [$patient, $queueEntry->encounter]));
    }
}
