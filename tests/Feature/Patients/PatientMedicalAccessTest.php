<?php

namespace Tests\Feature\Patients;

use App\Livewire\Encounters\EncounterShow;
use App\Models\Patient;
use App\Models\PatientMedicalInfo;
use App\Models\QueueEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PatientMedicalAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makePatientWithMedicalInfo(): Patient
    {
        $patient = Patient::create([
            'first_name' => 'Sara',
            'last_name' => 'Ibrahim',
            'sex' => 'female',
            'date_of_birth' => '1988-01-01',
            'phone' => '+251900000000',
        ]);

        PatientMedicalInfo::create([
            'patient_id' => $patient->id,
            'main_complaint' => 'Confidential clinical note',
        ]);

        return $patient;
    }

    protected function makeEncounterFor(Patient $patient, User $practitioner): \App\Models\Encounter
    {
        return QueueEntry::create([
            'patient_id' => $patient->id, 'practitioner_id' => $practitioner->id,
        ])->encounter;
    }

    public function test_practitioner_can_view_medical_record_on_the_encounter_page(): void
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $patient = $this->makePatientWithMedicalInfo();
        $encounter = $this->makeEncounterFor($patient, $practitioner);

        $this->actingAs($practitioner);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->assertSee('Confidential clinical note');
    }

    public function test_pharmacist_cannot_view_an_encounter_at_all(): void
    {
        $pharmacist = User::factory()->create();
        $pharmacist->assignRole('Pharmacist');
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $patient = $this->makePatientWithMedicalInfo();
        $encounter = $this->makeEncounterFor($patient, $practitioner);

        $this->actingAs($pharmacist)
            ->get(route('encounters.show', [$patient, $encounter]))
            ->assertForbidden();
    }

    public function test_a_user_with_view_only_access_cannot_edit_the_medical_record(): void
    {
        // No seeded role currently has medical.view without medical.update -
        // build that exact combination directly to prove the two permissions
        // are enforced independently.
        Permission::firstOrCreate(['name' => 'patients.medical.view', 'guard_name' => 'web']);
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('patients.medical.view');

        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $patient = $this->makePatientWithMedicalInfo();
        $encounter = $this->makeEncounterFor($patient, $practitioner);

        $this->actingAs($viewer);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->assertSee('Confidential clinical note')
            ->call('startEditingMedical')
            ->assertForbidden();
    }
}
