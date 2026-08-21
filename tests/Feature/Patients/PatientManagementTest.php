<?php

namespace Tests\Feature\Patients;

use App\Livewire\Patients\PatientEdit;
use App\Livewire\Patients\PatientIndex;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makePatient(array $overrides = []): Patient
    {
        return Patient::create(array_merge([
            'first_name' => 'Sara',
            'last_name' => 'Ibrahim',
            'sex' => 'female',
            'age' => 30,
            'phone' => '+251900000001',
            'region' => 'Oromia',
        ], $overrides));
    }

    public function test_reception_can_edit_patient_demographics(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');
        $patient = $this->makePatient();

        $this->actingAs($reception);

        Livewire::test(PatientEdit::class, ['patient' => $patient])
            ->set('phone', '+251911999999')
            ->set('region', 'Amhara')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('+251911999999', $patient->fresh()->phone);
        $this->assertSame('Amhara', $patient->fresh()->region);
    }

    public function test_practitioner_cannot_edit_patient_demographics(): void
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $patient = $this->makePatient();

        $this->actingAs($practitioner)
            ->get(route('patients.edit', $patient))
            ->assertForbidden();
    }

    public function test_practitioner_cannot_view_a_patient_with_a_pending_invoice(): void
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $patient = $this->makePatient();
        $patient->invoices()->create([
            'type' => 'registration', 'status' => 'pending', 'total_amount' => 100,
        ]);

        $this->actingAs($practitioner)
            ->get(route('patients.show', $patient))
            ->assertForbidden();
    }

    public function test_practitioner_can_view_a_patient_once_invoices_are_paid(): void
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $patient = $this->makePatient();
        $patient->invoices()->create([
            'type' => 'registration', 'status' => 'paid', 'total_amount' => 100,
        ]);

        $this->actingAs($practitioner)
            ->get(route('patients.show', $patient))
            ->assertOk();
    }

    public function test_practitioner_can_view_a_patient_already_queued_despite_an_unrelated_pending_invoice(): void
    {
        // A patient can be mid-treatment (already queued to a practitioner)
        // while a separate, unrelated invoice - e.g. a still-unpaid
        // registration fee - sits pending. That shouldn't lock the
        // practitioner back out of someone they're already seeing.
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $patient = $this->makePatient();
        $patient->invoices()->create([
            'type' => 'registration', 'status' => 'pending', 'total_amount' => 100,
        ]);
        \App\Models\QueueEntry::create([
            'patient_id' => $patient->id, 'practitioner_id' => $practitioner->id,
        ]);

        $this->actingAs($practitioner)
            ->get(route('patients.show', $patient))
            ->assertOk();
    }

    public function test_practitioner_patient_list_hides_patients_not_checked_in_to_them(): void
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $patient = $this->makePatient(['first_name' => 'Unconfirmed', 'phone' => '+251900000009']);
        $patient->invoices()->create([
            'type' => 'registration', 'status' => 'pending', 'total_amount' => 100,
        ]);

        $this->actingAs($practitioner);

        Livewire::test(PatientIndex::class)
            ->assertDontSee('Unconfirmed');
    }

    public function test_practitioner_patient_list_shows_only_patients_checked_in_and_assigned_to_them(): void
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $otherPractitioner = User::factory()->create();
        $otherPractitioner->assignRole('Practitioner');

        $assigned = $this->makePatient(['first_name' => 'AssignedToMe', 'phone' => '+251900000021']);
        \App\Models\QueueEntry::create(['patient_id' => $assigned->id, 'practitioner_id' => $practitioner->id]);

        $assignedToOther = $this->makePatient(['first_name' => 'AssignedToOther', 'phone' => '+251900000022']);
        \App\Models\QueueEntry::create(['patient_id' => $assignedToOther->id, 'practitioner_id' => $otherPractitioner->id]);

        $notCheckedIn = $this->makePatient(['first_name' => 'NotCheckedIn', 'phone' => '+251900000023']);

        $completed = $this->makePatient(['first_name' => 'AlreadyCompleted', 'phone' => '+251900000024']);
        \App\Models\QueueEntry::create([
            'patient_id' => $completed->id, 'practitioner_id' => $practitioner->id, 'status' => 'completed',
        ]);

        $this->actingAs($practitioner);

        Livewire::test(PatientIndex::class)
            ->assertSee('AssignedToMe')
            ->assertDontSee('AssignedToOther')
            ->assertDontSee('NotCheckedIn')
            ->assertDontSee('AlreadyCompleted');
    }

    public function test_practitioner_patient_list_shows_assigned_patients_regardless_of_registration_date(): void
    {
        // A practitioner's assigned queue isn't about when the patient was
        // registered - a revisit patient registered weeks ago can still be
        // checked in to them today, so the today/all registration-date scope
        // must not hide them.
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');

        $revisit = $this->makePatient(['first_name' => 'OldRevisit', 'phone' => '+251900000025']);
        $revisit->forceFill(['created_at' => now()->subDays(20)])->save();
        \App\Models\QueueEntry::create(['patient_id' => $revisit->id, 'practitioner_id' => $practitioner->id]);

        $this->actingAs($practitioner);

        Livewire::test(PatientIndex::class)
            ->assertSee('OldRevisit');
    }

    public function test_reception_patient_list_shows_names_regardless_of_payment_status(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');
        $patient = $this->makePatient(['first_name' => 'Unconfirmed', 'phone' => '+251900000010']);
        $patient->invoices()->create([
            'type' => 'registration', 'status' => 'pending', 'total_amount' => 100,
        ]);

        $this->actingAs($reception);

        Livewire::test(PatientIndex::class)
            ->assertSee('Unconfirmed');
    }

    public function test_clinic_manager_can_archive_a_patient(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $patient = $this->makePatient();

        $this->actingAs($manager);

        Livewire::test(PatientIndex::class)
            ->call('delete', $patient->id);

        $this->assertSoftDeleted('patients', ['id' => $patient->id]);
    }

    public function test_reception_cannot_archive_a_patient(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');
        $patient = $this->makePatient();

        $this->actingAs($reception);

        Livewire::test(PatientIndex::class)
            ->call('delete', $patient->id)
            ->assertForbidden();

        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'deleted_at' => null]);
    }

    public function test_archived_patients_do_not_appear_in_the_list(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $patient = $this->makePatient(['first_name' => 'Archived']);
        $patient->delete();

        $this->actingAs($manager);

        Livewire::test(PatientIndex::class)
            ->assertDontSee('Archived');
    }

    public function test_search_filters_by_name(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $this->makePatient(['first_name' => 'Findme', 'phone' => '+251900000002']);
        $this->makePatient(['first_name' => 'Other', 'phone' => '+251900000003']);

        $this->actingAs($manager);

        Livewire::test(PatientIndex::class)
            ->set('search', 'Findme')
            ->assertSee('Findme')
            ->assertDontSee('Other');
    }

    public function test_gender_filter_narrows_results(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $this->makePatient(['first_name' => 'FemalePatient', 'sex' => 'female', 'phone' => '+251900000004']);
        $this->makePatient(['first_name' => 'MalePatient', 'sex' => 'male', 'phone' => '+251900000005']);

        $this->actingAs($manager);

        Livewire::test(PatientIndex::class)
            ->set('gender', 'male')
            ->assertSee('MalePatient')
            ->assertDontSee('FemalePatient');
    }

    public function test_region_filter_narrows_results(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $this->makePatient(['first_name' => 'InOromia', 'region' => 'Oromia', 'phone' => '+251900000006']);
        $this->makePatient(['first_name' => 'InTigray', 'region' => 'Tigray', 'phone' => '+251900000007']);

        $this->actingAs($manager);

        Livewire::test(PatientIndex::class)
            ->set('region', 'Tigray')
            ->assertSee('InTigray')
            ->assertDontSee('InOromia');
    }

    public function test_patient_list_defaults_to_todays_registrations_only(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $this->makePatient(['first_name' => 'RegisteredToday', 'phone' => '+251900000011']);
        $yesterday = $this->makePatient(['first_name' => 'RegisteredYesterday', 'phone' => '+251900000012']);
        $yesterday->forceFill(['created_at' => now()->subDay()])->save();

        $this->actingAs($manager);

        Livewire::test(PatientIndex::class)
            ->assertSee('RegisteredToday')
            ->assertDontSee('RegisteredYesterday')
            ->set('scope', 'all')
            ->assertSee('RegisteredToday')
            ->assertSee('RegisteredYesterday');
    }
}
